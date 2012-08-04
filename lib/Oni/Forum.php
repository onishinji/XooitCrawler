<?php

namespace Oni;
class Forum {

  protected $newId;
  protected $url;
  protected $client;
  protected $bbUser;

  public function __construct($newId, $url) {
    $this->newId = $newId;
    $this->url = $url;
  }

  public function getNewId() {
    return $this->newId;
  }

  public function getUrl() {
    return $this->url;
  }

  public function setClient(\Goutte\Client $client) {
    $this->client = $client;
  }
  
  public function setBBUser($user)
  {
    $this->bbUser = $user;
  }

  /**
   * Must be overriden to match your html template
   */
  public function process() {

    $urlForum = $this->getUrl();
    $idForum = $this->getNewId();
    $client = $this->client;

    if (!$client) {
      throw new \Exception('You must attach a goutte client');
    }

    $crawler = $client->request('GET', $urlForum);

    // Page forum Links
    $goodLinksPageForum = array();

    // Main page
    $goodLinksPageForum[$crawler->filter('a.maintitle')->link()->getUri()] = $crawler->filter('a.maintitle')->link();

    // Navigation
    $linksPageForum = $crawler->filter('form span.gensmall a')->links();
    foreach ($linksPageForum as $linkPageForum) {

      if (strrpos($linkPageForum->getUri(), 'javascript') === FALSE && preg_match("#fr/f#", $linkPageForum->getUri())) {
        $goodLinksPageForum[$linkPageForum->getUri()] = $linkPageForum;
      }
    }

    foreach ($goodLinksPageForum as $linkPageForum) {

      $crawler = $client->click($linkPageForum);
      $links = $crawler->filter('a.topictitle')->links();

      foreach ($links as $link) {

        $crawler = $client->click($link);
        $post = $this->getOnePost($crawler, null, false);

        if ($post) {
          // Nav topics
          $nextPages = $crawler->filter(".bodyline span.nav a")->links();
          $urls = array();
          foreach ($nextPages as $subPage) {
            if ($subPage->getUri() != $link->getUri() && strrpos($subPage->getUri(), 'start') !== FALSE && strrpos($subPage->getUri(), 'javascript') === FALSE) {
              $urls[$subPage->getUri()] = $subPage;
            }
          }

          foreach ($urls as $subPage) {

            $crawler = $client->click($subPage);
            $post = $this->getOnePost($crawler, $post, true);
          }
          
          $post->attachToForumId($idForum);
          $post->setBBUser($this->bbUser);
          $post->save();
        }
      }
    }
  }

  /**
   * Must be overriden to match your html template
   * 
   * @param type $crawler
   * @param Oni\Post $post
   * @param type $topicCreate
   * @return Oni\Post 
   */
  protected function getOnePost($crawler, $post = null, $topicCreate = false) {

    $topicTR = $crawler->filter("table.forumline tr");

    foreach ($topicTR as $tr) {

      $username = false;
      $message = false;
      $title = false;

      foreach ($tr->childNodes as $td) {

        if ($td instanceof \DOMElement) {
          if ($td->getAttribute("valign") == "top") {
            $tdHTML = $this->innerHTML($td);

            // username
            preg_match_all("#r-id-[0-9]+\">(.*)</span></b></span>#", $tdHTML, $matches);
            if (isset($matches[1]) && !$username) {
              $username = $matches[1][0];
            }

            if (!$username) {
              preg_match('#<span class="name"><a name="(.*)"></a><b>(.*)</b></span>#', $tdHTML, $matches);
              if (isset($matches[2])) {
                $username = $matches[2];
              }
            }

            preg_match('#<!-- google_ad_section_start -->(.*)<!-- google_ad_section_end -->#', $tdHTML, $matches);
            if (isset($matches[1])) {


              preg_match("#<span class=\"postdetails\">Post&eacute; le: (.*)<span#", $tdHTML, $matchesDate);
              $date = $matchesDate[1];
              $date = str_replace("-", "", $date);
              $date = strtotime($date);

              if (!$date) {
                preg_match("#<span class=\"postdetails\">Post&eacute; le: Aujourd&rsquo;hui &agrave; (.*)<span#", $tdHTML, $matchesDate);
                if (count($matchesDate) > 0) {
                  $date = time();
                }

                preg_match("#<span class=\"postdetails\">Post&eacute; le: Hier &agrave; (.*)<span#", $tdHTML, $matchesDate);
                if (count($matchesDate) > 0) {
                  $date = strtotime("-1 day");
                }


                if (!$date) {
                  throw new Exception('Date format failed');
                }
              }



              $dom = new \DOMDocument();
              $dom->loadHTML($tdHTML);

              $list = $dom->getElementsByTagName('td');
              $message = $this->innerHTML($list->item(3));

              $message = preg_replace('#<span class="gensmall">(.*)</span>#', '', $message);
              $message = preg_replace('#<!-- google_ad_section_end -->(.*)#s', '', $message);

              $message .= "</span>";

              $title = $matches[1];
            }
          }
        }
      }

      if ($username && $message) {
        if (!$post) {
          $post = new Post($title, $message, $date, new User($username));
        } else {
          $post->addReply(new Reply($title, $message, $date, new User($username)));
        }
      }
    }

    return $post;
  }

  protected function innerHTML($el) {
    
    $doc = new \DOMDocument();
    $doc->appendChild($doc->importNode($el, TRUE));
    $html = trim($doc->saveHTML());
    $tag = $el->nodeName;
    return preg_replace('@^<' . $tag . '[^>]*>|</' . $tag . '>$@', '', $html);
  }

}

?>
