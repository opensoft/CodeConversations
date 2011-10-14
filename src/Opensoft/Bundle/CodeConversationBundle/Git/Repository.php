use Opensoft\Bundle\CodeConversationBundle\Git\Diff\DiffHeaderParser;
     * @return \Opensoft\Bundle\CodeConversationBundle\Model\Commit[]
        $command = "show --pretty=format:'%format%' %object%";
        } while($i < (count($output) - 1) && strpos($output[$i], 'diff --git') !== 0 && strpos($output[$i], 'diff --cc') !== 0);
            $diff = DiffHeaderParser::parse(array_slice($output, $i));
        return DiffHeaderParser::parse($this->repo->git(strtr($command, $parameters)));
    public function getTree($treeish, $path = null)
        $objects = array();
        $output = $this->repo->git(sprintf('ls-tree %s%s', $treeish, ':'.$path));
        if (!empty($output)) {
            foreach (explode("\n", $output) as $line) {
                $info = explode("\t", $line);
                $definition = explode(" ", $info[0]);
                $object = array();
                $object['mode'] = $definition[0];
                $object['type'] = $definition[1];
                $object['object'] = $definition[2];
                $object['file'] = $info[1];
                $objects[] = $object;
        }
        return $objects;
    }