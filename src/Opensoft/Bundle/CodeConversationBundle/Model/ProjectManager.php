<?php
/*
 *
 */

namespace Opensoft\Bundle\CodeConversationBundle\Model;

use Opensoft\Bundle\CodeConversationBundle\Git\Builder;

/**
 *
 *
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
abstract class ProjectManager implements ProjectManagerInterface
{
    protected $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }
    
    public function createProject()
    {
        $class = $this->getClass();

        return new $class();
    }

}
