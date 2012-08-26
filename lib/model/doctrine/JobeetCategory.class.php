<?php

/**
 * JobeetCategory
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    jobeet
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class JobeetCategory extends BaseJobeetCategory
{
    public function getActiveJobsQuery()
    {
      $q = Doctrine_Query::create()
        ->from('JobeetJob j')
        ->where('j.category_id = ?', $this->getId());
     
      return Doctrine_Core::getTable('JobeetJob')->addActiveJobsQuery($q);
    }

    public function getActiveJobs($max = 10)
    {
      $q = $this->getActiveJobsQuery()
        ->limit($max);
     
      return $q->execute();
    }
     
    public function countActiveJobs()
    {
      return $this->getActiveJobsQuery()->count();
    }
}
