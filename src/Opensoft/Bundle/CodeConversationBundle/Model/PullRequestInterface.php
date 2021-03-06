<?php
/*
 *
 */

namespace Opensoft\Bundle\CodeConversationBundle\Model;

use Redpanda\Bundle\ActivityStreamBundle\Streamable\StreamableInterface;

/**
 *
 *
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface PullRequestInterface extends StreamableInterface
{
    /**
     * @param int $id
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getMergeBase();

    /**
     * @param string $mergeBase
     */
    public function setMergeBase($mergeBase);

    /**
     * @param \Opensoft\Bundle\CodeConversationBundle\Model\PullRequestComment[]
     */
    public function setComments(array $comments);

    /**
     * @return \Opensoft\Bundle\CodeConversationBundle\Model\PullRequestComment[]
     */
    public function getComments();

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @param string $description
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param \Opensoft\Bundle\CodeConversationBundle\Model\BranchInterface $baseBranch
     */
    public function setBaseBranch($baseBranch);

    /**
     * @return \Opensoft\Bundle\CodeConversationBundle\Model\BranchInterface
     */
    public function getBaseBranch();

    /**
     * @param \Opensoft\Bundle\CodeConversationBundle\Model\UserInterface $author
     */
    public function setAuthor(UserInterface $author);

    /**
     * @return \Opensoft\Bundle\CodeConversationBundle\Model\UserInterface
     */
    public function getAuthor();

    /**
     * @param \Opensoft\Bundle\CodeConversationBundle\Model\ProjectInterface $project
     */
    public function setProject($project);

    /**
     * @return \Opensoft\Bundle\CodeConversationBundle\Model\ProjectInterface
     */
    public function getProject();

    /**
     * @param \Opensoft\Bundle\CodeConversationBundle\Model\BranchInterface $headBranch
     */
    public function setHeadBranch($headBranch);

    /**
     * @return \Opensoft\Bundle\CodeConversationBundle\Model\BranchInterface
     */
    public function getHeadBranch();

    /**
     * @param int $status
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @return string|null
     */
    public function getStatusCode();

    /**
     * @return array
     */
    public static function getStatusList();

    /**
     * @param string $title
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getTitle();
}
