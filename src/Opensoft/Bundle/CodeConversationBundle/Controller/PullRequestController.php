<?php

namespace Opensoft\Bundle\CodeConversationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Opensoft\Bundle\CodeConversationBundle\Model\PullRequestManagerInterface;
use Opensoft\Bundle\CodeConversationBundle\Model\ProjectInterface;
use Opensoft\Bundle\CodeConversationBundle\Form\Type\PullRequestFormType;
use Opensoft\Bundle\CodeConversationBundle\Form\Type\PullRequestCommentFormType;
use Opensoft\Bundle\CodeConversationBundle\Model\PullRequestInterface;
use Opensoft\Bundle\CodeConversationBundle\Model\PullRequest;
use Opensoft\Bundle\CodeConversationBundle\Timeline\EventTimeline;
use Opensoft\Bundle\CodeConversationBundle\Entity\PullRequestComment;

/**
 * @ParamConverter("project", class="Opensoft\Bundle\CodeConversationBundle\Model\ProjectInterface")
 * @ParamConverter("remote", class="Opensoft\Bundle\CodeConversationBundle\Model\RemoteInterface")
 * @ParamConverter("branch", class="Opensoft\Bundle\CodeConversationBundle\Model\BranchInterface")
 * @ParamConverter("pullRequest", class="Opensoft\Bundle\CodeConversationBundle\Model\PullRequestInterface")
 */
class PullRequestController extends Controller
{
    /**
     * @return \Opensoft\Bundle\CodeConversationBundle\Model\PullRequestManagerInterface
     */
    public function getPullRequestManager()
    {
        return $this->container->get('opensoft_codeconversation.manager.pull_request');
    }

    /**
     * @Route("/{projectSlug}/pulls/new")
     * @Template()
     */
    public function createAction(ProjectInterface $project)
    {
        $pullRequestManager = $this->getPullRequestManager();

        $pullRequest = $pullRequestManager->createPullRequest();
        $pullRequest->setProject($project);
        $pullRequest->setCreatedAt(new \DateTime());
        $pullRequest->setAuthor($this->container->get('security.context')->getToken()->getUser());
        $pullRequest->setStatus(PullRequest::STATUS_OPEN);


        $form = $this->createForm(new PullRequestFormType($project), $pullRequest);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {

                /** @var \Opensoft\Bundle\CodeConversationBundle\Git\RepositoryManager $repositoryManager */
                $repositoryManager = $this->get('opensoft_codeconversation.repository_manager');
                $repository = $repositoryManager->getRepository($project);

                $mergeBase = $repository->getMergeBase($pullRequest->getSourceBranch()->getFullName(), $pullRequest->getDestinationBranch()->getFullName());
                $pullRequest->setMergeBase($mergeBase);
                
                $pullRequestManager->updatePullRequest($pullRequest);


                /** @var \Redpanda\Bundle\ActivityStreamBundle\Entity\ActionManager $activityManager  */
                $activityManager = $this->container->get('activity_stream.action_manager');
                $activityManager->send('opened', $pullRequest, $project);


                $this->get('session')->setFlash('success', 'Pull request engaged.');

                return $this->redirect($this->generateUrl('opensoft_codeconversation_pullrequest_view', array('pullId' => $pullRequest->getId(), 'projectSlug' => $project->getSlug())));
            }
        }

        return array('project' => $project, 'form' => $form->createView());
    }

    /**
     * @Route("/{projectSlug}/pulls")
     * @Template()
     */
    public function listAction(ProjectInterface $project)
    {
        return array('project' => $project, 'pullRequests' => $this->getPullRequestManager()->findPullRequestBy(array('project' => $project->getId()), array('createdAt' => 'DESC')));
    }

    /**
     * @Route("/{projectSlug}/pull/{pullId}")
     * @Template()
     */
    public function viewAction(ProjectInterface $project, PullRequestInterface $pullRequest)
    {
        /** @var \Opensoft\Bundle\CodeConversationBundle\Git\RepositoryManager $repositoryManager */
        $repositoryManager = $this->get('opensoft_codeconversation.repository_manager');
        $repository = $repositoryManager->getRepository($project);
//

        $fullDiff = $repository->getDiff($pullRequest->getMergeBase(), $pullRequest->getSourceBranch()->getFullName());
        $commits = $repository->getCommits($pullRequest->getMergeBase(), $pullRequest->getSourceBranch()->getFullName());

        $timeline = new EventTimeline();
        foreach ($commits as $commit) {
            $timeline->insert($commit);
        }
        foreach ($pullRequest->getComments() as $comment)
        {
            $timeline->insert($comment);
        }


        /** @var \Redpanda\Bundle\ActivityStreamBundle\Entity\ActionManager $activityManager  */
        $activityManager = $this->container->get('activity_stream.action_manager');
        $stream = $activityManager->findStreamByTarget($pullRequest);
//        $activityManager->


        /** @var \Gedmo\Loggable\Entity\LogEntry[] $logs  */
//        $logs = $em->getRepository('StofDoctrineExtensionsBundle:LogEntry')->findBy(array('objectClass' => get_class($pullRequest), 'objectId' => $pullRequest->getId()));
//        foreach ($logs as $logEntry) {
//            $timeline->insert($logEntry);
//        }

        $form = $this->createForm(new PullRequestCommentFormType(), new PullRequestComment());

        return array(
            'project' => $project,
            'pullRequest' => $pullRequest,
            'eventTimeline' => $timeline,
            'commits' => $commits, 
            'form' => $form->createView(),
            'fullDiff' => $fullDiff,
            'stream' => $stream,
//            'commits' => $commits,
//            'timeline' => $timeline
        );
    }
}
