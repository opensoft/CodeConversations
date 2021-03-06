<?php
/*
 *
 */

namespace Opensoft\Bundle\CodeConversationBundle\Model;

/**
 *
 *
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface CommitInterface
{
    public function setId($id);

    public function getId();

    public function setProject(ProjectInterface $project);

    public function getProject();

    public function setTree($tree);

    public function getTree();

    public function setAuthorEmail($authorEmail);

    public function getAuthorEmail();

    public function setAuthorName($authorName);

    public function getAuthorName();

    public function setAuthoredDate(\DateTime $authoredDate);

    public function getAuthoredDate();

    public function setCommittedDate(\DateTime $committedDate);

    public function getCommittedDate();

    public function setCommitterEmail($committerEmail);

    public function getCommitterEmail();

    public function setCommitterName($committerName);

    public function getCommitterName();

    public function setMessage($message);

    public function getMessage();

    public function setParents(array $parents);

    public function getParents();

    public function getDiff();

    public function setDiff(DiffInterface $diff);
}
