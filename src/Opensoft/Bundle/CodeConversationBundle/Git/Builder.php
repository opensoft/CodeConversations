use Opensoft\Bundle\CodeConversationBundle\Model\DiffChunk;
use Opensoft\Bundle\CodeConversationBundle\Model\Commit;
        $format = '%H%n%s%n%ci%n%at%n%P%n%b';
        $commit = new Commit();
        $commit->setSha1($output[0]);
        $commit->setMessage($output[1]);
        $commit->setAuthor($output[2]);
        $commit->setTimestamp(new \DateTime(strtotime($output[3])));

        // Detect merge parent
        if (strpos($output[4], " ") > 0) {
            $merge = explode(" ", $output[4]);
            $commit->setParent($merge[0]);
            $commit->setMergeParent($merge[1]);
        } else {
            $commit->setParent($output[4]);
        }
        
        if (isset($output[6])) {
            $diff = null;
            $diffChunk = null;
            $diffChunkContent = array();
            $i = 6;
            do {
                $line = $output[$i++];

                // start a new Diff object
                if (strpos($line, 'diff --git ') === 0) {
    //                print_r($i . "\n");

                    // Clean up old diff object, if there is one
                    if (null !== $diff) {
                        if (null !== $diffChunk) {
                            if (!empty($diffChunkContent)) {
                                $diffChunk->setContent($diffChunkContent);
                            }

                            $diff->addDiffChunk($diffChunk);
                            $diffChunk = null;
                        }

                        $commit->addFileDiff($diff);
                    }
                    $diff = new Diff();

                    list($srcFileName, $dstFileName) = explode(" ", trim(substr($line, 11)));
                    $diff->setSrcPath(substr($srcFileName, 2));
                    $diff->setDstPath(substr($dstFileName,2));

    //                // Parse extended header lines
                    do {
                        $line = $output[$i++];

                        if (strpos($line, 'old mode ') === 0) {
                            $diff->setSrcMode(substr($line, 8));
                        } elseif (strpos($line, 'new mode ') === 0) {
                            $diff->setDstMode(substr($line, 8));
                        } elseif (strpos($line, 'deleted file mode') === 0) {
                            $diff->setDstMode(substr($line, 18));
                            $diff->setStatus(Diff::STATUS_DELETION);
                        } elseif (strpos($line, 'new file mode ') === 0) {
                            $diff->setDstMode(substr($line, 14));
                            $diff->setStatus(Diff::STATUS_ADDITION);
                        } elseif (strpos($line, 'copy from ') === 0) {
                            $diff->setSrcPath(substr($line, 10));
                            $diff->setStatus(Diff::STATUS_COPY);
                        } elseif (strpos($line, 'copy to ') === 0) {
                            $diff->setDstPath(substr($line, 8));
                            $diff->setStatus(Diff::STATUS_COPY);
                        } elseif (strpos($line, 'rename from ') === 0) {
                            $diff->setSrcPath(substr($line, 12));
                            $diff->setStatus(Diff::STATUS_RENAMING);
                        } elseif (strpos($line, 'rename to ') === 0) {
                            $diff->setDstPath(substr($line, 10));
                            $diff->setStatus(Diff::STATUS_RENAMING);
                        } elseif (strpos($line, 'index ') === 0) {
                            if (strpos(substr($line, 6), ' ') > 0) {
    //                            print_r($line);
    //                            print_r(substr($line, 6));
    //                            print_r(explode(" ", substr($line, 6)));
    //                            die();
                                list($hash, $mode) = explode(" ", substr($line, 6));
                                $diff->setSrcMode($mode);
                                $diff->setDstMode($mode);
                                $diff->setStatus(Diff::STATUS_MODIFICATION);
                            } else {
                                $hash = substr($line, 6);
                            }
                            list($srcHash, $dstHash) = explode("..", $hash);

                            $diff->setSrcSha1($srcHash);
                            $diff->setDstSha1($dstHash);
                        }
                    } while ($i < count($output) && strpos($output[$i], '---') !== 0);
    //
    //                 Parse from-file/to-file header
                    if ($i < count($output)) {
                        do {
                            $line = $output[$i++];

                            if (strpos($line, '--- ') === 0) {
                                $diff->setSrcPath(substr($line, 6));
                            } elseif (strpos($line, '+++ ') === 0 ) {
                                $diff->setDstPath(substr($line, 6));
                            }
                        } while ($i < count($output) && strpos($output[$i], '@@') !== 0);
                    }
                }

                // Parse for diff chunk header
    //            print_r($line."\n");
                if (strpos($line, '@@') === 0) {
    //                print_r("**** DIFF CHUNK DETECTED *****\n");
                    if (null !== $diffChunk) {
                        if (!empty($diffChunkContent)) {
                            $diffChunk->setContent($diffChunkContent);
                        }
                        $diff->addDiffChunk($diffChunk);
                    }

                    $diffChunk = new DiffChunk();
                    $diffChunk->setDescription(trim($line));
                    $diffChunkContent = array();

                    $diffChunkHeader = trim(str_replace(array('@@ ', ' @@'), '', substr($line, 0, strpos($line, ' @@'))));
                    $array = explode(" ", $diffChunkHeader);
                    $src = explode(",", substr($array[0], 1));
                    $dst = explode(",", substr($array[1], 1));

                    $diffChunk->setSrcStartLineNumber($src[0]);
                    $diffChunk->setDstStartLineNumber($dst[0]);
                } else {
                    $diffChunkContent[] = $line;
                }

    //            $file[] = $line;



    //            $i++;
            } while ($i < count($output));

            if (null !== $diff) {
                if (null !== $diffChunk) {
                    if (!empty($diffChunkContent)) {
                        $diffChunk->setContent($diffChunkContent);
                    }

                    $diff->addDiffChunk($diffChunk);

                $commit->addFileDiff($diff);


//        $commit['diffs'] = $diffs;
//        print_r($command."\n");