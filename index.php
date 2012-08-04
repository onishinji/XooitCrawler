<?php

/*
  RAZ SQL

  UPDATE `forum`.`phpbb_forums` SET `forum_posts` = '', `forum_topics` = '', `forum_topics_real` = '', `forum_last_post_id` = '', `forum_last_poster_id` = '';
  truncate phpbb_topics;
  truncate phpbb_posts;
  truncate phpbb_search_results;
  truncate phpbb_search_wordlist;
  truncate phpbb_search_wordmatch;

 */

require 'config.php';

use Goutte\Client;

// PHP STUF
$user->session_begin();
$user->data['user_id'] = $phpAdmin['userId'];
$auth->acl($user->data);
$user->setup('ucp');

// Crawler
$client = new Client();
$crawler = $client->request('GET', $loginUrl);
$form = $crawler->selectButton('Connexion')->form();
$form['username'] = $phpAdmin['username'];
$form['password'] = $phpAdmin['password'];

// login
$client->submit($form);

foreach ($forums as $forum) {
  $forum->setClient($client);
  $forum->setBBUser($user);
  $forum->process();
}

?>
