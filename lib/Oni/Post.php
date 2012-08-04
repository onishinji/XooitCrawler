<?php

namespace Oni;

/**
 * Description of Post
 *
 * @author guillaume
 */
class Post {

  public $topic_id;
  private $forumId;
  private $title;
  private $content;
  private $userId;
  private $replies = array();
  
  private $url;
  private $username;
  private $phpbbUser;
  private $dateTime;
  
  protected $mode = 'post';

  public function __construct($title, $content, $datetime, \Oni\User $user) {
    $this->title = $title;
    $this->content = $content;
    $this->userId = $user->userId;
    $this->username = $user->username;
    $this->dateTime = $datetime;
  }
  
  public function attachToForumId($forum_id)
  {
    $this->forumId = $forum_id;
  }
  
  public function setBBUser($user)
  {
    $this->phpbbUser = $user;
  }

  public function addReply(Reply $reply) {
    $reply->id = $this->id;
    $this->replies[] = $reply;
  }

  public function save() {

    $my_subject = $this->title;
    $my_text = $this->content;

    $my_text = str_replace('postbody', '', $my_text);
    

    $poll = $uid = $bitfield = $options = '';

    generate_text_for_storage($my_subject, $uid, $bitfield, $options, false, false, false);
    generate_text_for_storage($my_text, $uid, $bitfield, $options, true, true, true);


    $data = array(
      'forum_id' => $this->forumId,
      'icon_id' => false,
      'force_approved_state' => true,
      'enable_bbcode' => true,
      'enable_smilies' => true,
      'enable_urls' => true,
      'enable_sig' => true,
      'message' => $my_text,
      'message_md5' => md5($my_text),
      'bbcode_bitfield' => $bitfield,
      'bbcode_uid' => $uid,
      'post_edit_locked' => 0,
      'topic_title' => $my_subject,
      'notify_set' => false,
      'notify' => false,
      'post_time' => 0,
      'forum_name' => '',
      'enable_indexing' => true,
      'topic_id' => $this->topic_id,
      'user_id'      => $this->userId,
      'is_registered' => true,
      'username' => $this->username,
      'time' => $this->dateTime
    );
    
    if(!$this->dateTime)
    {
      die("error");
    }

    $this->phpbbUser->data['user_id'] = $this->userId;
    $this->phpbbUser->data['username'] = $this->username;
    
    $return = submit_post($this->mode, $my_subject, '', POST_NORMAL, $poll, $data);
    $this->url = $return;
    
    $this->saveReplies();
  }
  
  private function saveReplies()
  {
    $pId = $this->computeTopicId();
    
    foreach($this->replies as $reply)
    {
      $reply->topic_id = $pId;
      $reply->forumId = $this->forumId;
      $reply->setBBUser($this->phpbbUser);
      $reply->save();
    }
    
  }
  
  public function getReplies()
  {
    return $this->replies;
  }
  
  public function computeTopicId()
  {
    $url = $this->url;
    preg_match("&t=([0-9]*)&", $url, $matches); 
    return $matches[1];
  }

}

?>
