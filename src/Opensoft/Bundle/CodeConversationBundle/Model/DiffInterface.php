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
interface DiffInterface
{

    public function getInsertions();

    public function getDeletions();

    /**
     * @return FileDiffInterface[]
     */
    public function getFileDiffs();

    /**
     * @param FileDiffInterface $fileDiff
     */
    public function addFileDiff(FileDiffInterface $fileDiff);

    /**
     * @param FileDiffInterface[] $fileDiffs
     */
    public function setFileDiffs(array $fileDiffs);
}
