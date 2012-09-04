<?php
include(dirname(__FILE__).'/../../bootstrap/functional.php');
 
$browser = new JobeetTestFunctional(new sfBrowser());
$browser->loadData();
 
$browser->info('1 - The homepage')->
  get('/')->
  with('request')->begin()->
    isParameter('module', 'job')->
    isParameter('action', 'index')->
  end()->
  with('response')->begin()->
    info('  1.1 - Expired jobs are not listed')->
    checkElement('.jobs td.position:contains("expired")', false)->
  end()
;
 
$max = sfConfig::get('app_max_jobs_on_homepage');
 
$browser->info('1 - The homepage')->
  info(sprintf('  1.2 - Only %s jobs are listed for a category', $max))->
  with('response')->
    checkElement('.category_programming tr', $max)
;
 
$browser->info('1 - The homepage')->
  get('/')->
  info('  1.3 - A category has a link to the category page only if too many jobs')->
  with('response')->begin()->
    checkElement('.category_design .more_jobs', false)->
    checkElement('.category_programming .more_jobs')->
  end()
;
 
$browser->info('1 - The homepage')->
  info('  1.4 - Jobs are sorted by date')->
  with('response')->begin()->
    checkElement(sprintf('.category_programming tr:first a[href*="/%d/"]', $browser->getMostRecentProgrammingJob()->getId()))->
  end()
;
 
$job = $browser->getMostRecentProgrammingJob();
 
$browser->info('2 - The job page')->
  get('/')->
 
  info('  2.1 - Each job on the homepage is clickable and give detailed information')->
  click('Web Developer', array(), array('position' => 1))->
  with('request')->begin()->
    isParameter('module', 'job')->
    isParameter('action', 'show')->
    isParameter('company_slug', $job->getCompanySlug())->
    isParameter('location_slug', $job->getLocationSlug())->
    isParameter('position_slug', $job->getPositionSlug())->
    isParameter('id', $job->getId())->
  end()->
 
  info('  2.2 - A non-existent job forwards the user to a 404')->
  get('/job/foo-inc/milano-italy/0/painter')->
  with('response')->isStatusCode(404)->
 
  info('  2.3 - An expired job page forwards the user to a 404')->
  get(sprintf('/job/sensio-labs/paris-france/%d/web-developer', $browser->getExpiredJob()->getId()))->
  with('response')->isStatusCode(404)
;

$browser->info('3 - Post a Job page')->
  info('  3.1 - Submit a Job')->
 
  get('/job/new')->
  with('request')->begin()->
    isParameter('module', 'job')->
    isParameter('action', 'new')->
  end()->
 
  click('Preview your job', array('job' => array(
    'type'         => 'full-time',
    'company'      => 'Sensio Labs',
    'url'          => 'http://www.sensio.com/',
    'logo'         => sfConfig::get('sf_upload_dir').'/jobs/sensio-labs.gif',
    'position'     => 'Developer',
    'location'     => 'Atlanta, USA',
    'description'  => 'You will work with symfony to develop websites for our customers.',
    'how_to_apply' => 'Send me an email',
    'email'        => 'for.a.job@example.com',
    'is_public'    => false,
  )))->
 
  with('request')->begin()->
    isParameter('module', 'job')->
    isParameter('action', 'create')->
  end()->

  with('form')->begin()->
  hasErrors(false)->
  end()->

  with('response')->isRedirected()->
  followRedirect()->
   
  with('request')->begin()->
    isParameter('module', 'job')->
    isParameter('action', 'show')->
  end()
;

$browser->setTester('doctrine', 'sfTesterDoctrine');

$browser->
with('doctrine')->begin()->
  check('JobeetJob', array(
    'location'     => 'Atlanta, USA',
    'is_activated' => false,
    'is_public'    => false,
  ))->
end();

$browser->
  info('  3.2 - Submit a Job with invalid values')->
 
  get('/job/new')->
  click('Preview your job', array('job' => array(
    'company'      => 'Sensio Labs',
    'position'     => 'Developer',
    'location'     => 'Atlanta, USA',
    'email'        => 'not.an.email',
  )))->
 
  with('form')->begin()->
    hasErrors(3)->
    isError('description', 'required')->
    isError('how_to_apply', 'required')->
    isError('email', 'invalid')->
  end()
;
