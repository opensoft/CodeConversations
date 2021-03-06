<?php
/*
 *
 */

namespace Opensoft\Bundle\CodeConversationBundle\Request\ParamConverter;

use Opensoft\Bundle\CodeConversationBundle\Model\ProjectManagerInterface;
use Opensoft\Bundle\CodeConversationBundle\Model\RemoteManagerInterface;
use Opensoft\Bundle\CodeConversationBundle\Model\BranchManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 *
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class ProjectParamConverter implements ParamConverterInterface
{
    /**
     * @var \Opensoft\Bundle\CodeConversationBundle\Model\ProjectManagerInterface
     */
    protected $projectManager;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param \Opensoft\Bundle\CodeConversationBundle\Model\ProjectManagerInterface $projectManager
     * @param string $class
     */
    public function __construct(ProjectManagerInterface $projectManager, $class)
    {
        $this->projectManager = $projectManager;
        $this->class = $class;
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface $configuration
     */
    function apply(Request $request, ConfigurationInterface $configuration)
    {
        if (!$request->attributes->has('projectName')) {
            return;
        }

        $name = $request->attributes->get('projectName');
        $project = $this->projectManager->findProjectByName($name);

        if (null === $project) {
            throw new NotFoundHttpException(sprintf('Project with name "%s" was not found.', $name));
        }

        $request->attributes->set($configuration->getName(), $project);
    }

    /**
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface $configuration
     * @return bool
     */
    function supports(ConfigurationInterface $configuration)
    {
        return $configuration->getClass() === $this->class;
    }

}
