<?php
/*
 *
 */

namespace Opensoft\Bundle\CodeConversationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Opensoft\Bundle\CodeConversationBundle\Entity\Project;
use Opensoft\Bundle\CodeConversationBundle\Entity\Branch;
use Opensoft\Bundle\CodeConversationBundle\SourceCode\RepositoryInterface;
use Doctrine\ORM\EntityManager;

/**
 * Synchronizes database with git repositories for projects
 *
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class SynchronizeCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this->setName('opensoft:code:sync');
        $this->addArgument('name', InputArgument::OPTIONAL, 'The project\'s name');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Opensoft\Bundle\CodeConversationBundle\Model\ProjectManagerInterface $projectManager */
        $projectManager = $this->getContainer()->get('opensoft_codeconversation.manager.project');

        if ($projectName = $input->getArgument('name')) {
            $projects = array($projectManager->findProjectBy(array('name'=>$projectName)));
        } else {
            $projects = $projectManager->findProjects();
        }

        if (empty($projects)) {
            if ($projectName = $input->getArgument('name')) {
                $output->writeln(strtr('<error>Could not find project "%project%"</error>', array('%project%' => $projectName)));
            } else {
                $output->writeln('<error>There are no projects configured...</error>');
            }

            return 1;
        }

        foreach ($projects as $project) {
            $output->writeln(strtr('Synchronizing project "<info>%project%</info>...', array('%project%' => $project->getName())));

            $project->initSourceCodeRepo(function ($type, $buffer) use ($output) {
                if ('err' === $type) {
                    $output->write(str_replace("\n", "\nERR| ", $buffer));
                } else {
                    $output->write(str_replace("\n", "\nOUT| ", $buffer));
                }
            });

            $project->synchronizeBranches();

            // Loop through pull requests and check for "merged" pull requests (ones with zero commit difference?)

            $projectManager->updateProject($project);

            $output->writeln('');
            $output->writeln(strtr('Synchronization complete "<info>%project%</info>"', array('%project%' => $project->getName())));
        }
    }

}
