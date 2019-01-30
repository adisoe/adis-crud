<?php

class AdisARModel extends ActiveRecord\Model {

  static $table_name = 'mgartjual';
  static $custom_pk = 'IdTJual';

  static function getByID($pk, $id) {
    $result = self::all(array(
                'conditions' => $pk . " = '" . $id . "' ",
                'limit' => 1
    ));
    if ($result) {
      return $result[0];
    }
    return null;
  }

  static function getChild($id) {
    $result = AdisDetailARModel::find('all', array(
                'conditions' => self::$custom_pk." = '$id'"
    ));
    return $result;
  }

  static function deleteChild($pk, $id) {
    $sql = "DELETE FROM " . AdisDetailARModel::$table_name . "      
      WHERE " . $pk . "='" . $id . "'";
    //echo $sql;die();
    $connection = ActiveRecord\ConnectionManager::get_connection();
    $result = $connection->query($sql);
  }

  static function countChild($id) {
    $sql = "select count(".self::$custom_pk.") as total
      from " . AdisDetailARModel::$table_name . "
      where ".self::$custom_pk." = '$id'";
    $result = self::find_by_sql($sql);
    if ($result) {
      return $result[0]->total;
    }
    return 0;
  }

  static function getByUsername($username) {
    $result = UserAR::find('last', array(
                'conditions' => "username = '$username'"
    ));
    return $result;
  }

  static function getByNama($nama) {
    $result = UserAR::find('last', array(
                'conditions' => "nama = '$nama'"
    ));
    return $result;
  }

  static function encryptPassword($password) {
    $scrambler = "PadmaTiRt4";
    $user_password = addslashes(md5($scrambler . md5($password) . $scrambler));
    return $user_password;
  }

  static function login($email, $password) {
    // check password length    
    $valid = true;
    $msg = '';


    $member = self::getByUsername($email);

    if (!$member) {
      $valid = false;
    } else {
      $pass_encrypt = self::encryptPassword($password);
      $member = UserAR::find('last', array(
                  'conditions' => "username = '$email' AND password='$pass_encrypt' "
      ));
    }
    if (!$member) {
      $msg .= 'Login failed, check again your email and password.';
      $valid = false;
    }

    return array('Valid' => $valid, 'Message' => $msg);
  }

  function sendAndroidNotification($data, $exclude_token = '') {
    $member_android_id = MemberAndroidData::getMemberAndroidID($this->id_user, $exclude_token);
    //CT::writeSimpleLog('user sendAndroidNotification '.var_export($member_android_id,true));
    if (!sizeof($member_android_id) || !count($member_android_id)) {
      return false;
    }
    return self::sendAndroidNotificationAll($member_android_id, $data, $exclude_token);
  }

  static function sendAndroidNotificationAll($arr_android_id, $data, $exclude_token = '') {
    $post = array(
        'registration_ids' => $arr_android_id,
        'data' => $data,
    );
    $post = json_encode($post);
    $header = array(
        'Authorization:key=' . Page::$gcm_api_key,
        'Content-Type: application/json'
    );
    $result = CT::curlPostJson('https://fcm.googleapis.com/fcm/send', $post, (string) '', $header);

    // check canonical_ids
    $result_arr = json_decode($result, true);
    if (isset($result_arr['canonical_ids']) && $result_arr['canonical_ids'] && isset($result_arr['results'])) {
      foreach ($result_arr['results'] as $idx => $row) {
        if (isset($row['registration_id'])) {
          MemberAndroidData::updateMemberAndroidID($this->ID, $member_android_id[$idx], 0);
        }
      }
    }

    if (!$result_arr['success']) {
      CT::writeSimpleLog($result);
    }

    /* CT::sendPlainEmail('crosstechno.report@gmail.com', 'sendAndroidNotification', '', 
      'real id: '.implode(' | ', $member_android_id)
      .' >>>>> '.$result); */

    return $result;
  }

  static function getUserUnderMe($id_user, $keyword = '', $is_direct = false) {
    $where = "";
    $max_level = 6;
    if ($is_direct) {
      $max_level = 1;
    }
    for ($i = 1; $i <= $max_level; $i++) {
      if ($where) {
        $where .= " OR ";
      }
      $where .= " id_user IN (
                          SELECT id_user 
                          FROM tbl_grade 
                          WHERE up" . $i . "='$id_user' 
                          ) ";
    }
    if ($where) {
      if ($is_direct) {
        $where = " AND (" . $where . ") ";
      } else {
        $where = " AND (" . $where . " OR id_user IN ($id_user) )";
      }
    }
    if ($keyword) {
      $where = " AND (username LIKE '%$keyword%' OR nama LIKE '%$keyword%') ";
    }

    $member = UserAR::find('all', array(
                'conditions' => "id_user > 0 $where ",
                'order' => 'nama asc, username asc'
    ));
    return $member;
  }

  static function getUserControlled($id_user) {
    $where = "";
    $where .= " AND id_user IN (
                        SELECT user_controlled
                        FROM tbl_pengawas 
                        WHERE user_pengawas='$id_user' 
                      ) ";

    $member = UserAR::find('all', array(
                'conditions' => "id_user > 0 $where ",
                'order' => 'nama asc, username asc'
    ));
    return $member;
  }

  function getVisitThisMonthPerDay() {
    $month = date('m');
    $year = date('Y');

    $sql = "select date(tgl_visitor) as tgl_visitor, count(id_visitor) as total_visit
      from tbl_visitor
      where email like '" . $this->username . "'
        and MONTH(tgl_visitor)='$month'
        and YEAR(tgl_visitor)='$year'
      group by date(tgl_visitor)
      order by tgl_visitor asc";
    $result = self::find_by_sql($sql);
    return $result;
  }

  function generateUniqueCode($client_secret) {
    $code = addslashes(md5($this->id_user . md5($client_secret) . $client_secret));
    return $code;
  }

  function setUserOnline() {
    $sql = "UPDATE `tbl_user_online`
      SET online_date='" . CT::currentDatetimeMySQL() . "'
          , online_jam='" . date('H:i:s') . "'
          , status_login='1'
      WHERE id_user='" . $this->id_user . "'";
    $connection = ActiveRecord\ConnectionManager::get_connection();
    $result = $connection->query($sql);

    // get all user
    $user_online_ids = UserAR::getUserOnline();
    $android_ids = MemberAndroidData::getByUserIDs($user_online_ids);
    if (count($android_ids)) {
      // send notif    
      $android_data = array();
      $android_data['Type'] = CT::NOTIF_TYPE_USER_ONLINE_OFFLINE;
      $android_data['Title'] = CT::NOTIF_TYPE_USER_ONLINE_OFFLINE;
      $android_data['Content'] = $this->nama . ' is online';
      $android_data['id_user'] = $this->id_user;
      $android_data['is_online'] = 1;
      self::sendAndroidNotificationAll($android_ids, $android_data);
    }
  }

  function setUserOffline() {
    $sql = "UPDATE `tbl_user_online`
      SET offline_date='" . CT::currentDatetimeMySQL() . "'
          , offline_jam='" . date('H:i:s') . "'
          , status_login='0'
      WHERE id_user='" . $this->id_user . "'";
    $connection = ActiveRecord\ConnectionManager::get_connection();
    $result = $connection->query($sql);
    //$result = self::find_by_sql($sql);
    // get all user
    $android_ids = MemberAndroidData::getAllDataArray();
    if (count($android_ids)) {
      // send notif    
      $android_data = array();
      $android_data['Type'] = CT::NOTIF_TYPE_USER_ONLINE_OFFLINE;
      $android_data['Title'] = CT::NOTIF_TYPE_USER_ONLINE_OFFLINE;
      $android_data['Content'] = $this->nama . ' is offline';
      $android_data['id_user'] = $this->id_user;
      $android_data['is_online'] = 0;
      self::sendAndroidNotificationAll($android_ids, $android_data);
    }
  }

  static function setUsersOffline() {
    /* $date = time()-(self::$limit_online * 60);
      $date = date('Y-m-d H:i:s', $date);

      $sql = "select U.id_user, O.online_date, IF(online_date < '$date', 0, 1) AS is_online
      from `user` U
      join tbl_user_online O ON O.id_user=U.id_user
      order by is_online desc, online_date desc, U.nama asc";
      $result = self::find_by_sql($sql); */
  }

  static function getUserOnline() {
    $date = time() - (self::$limit_online * 60);
    $date = date('Y-m-d H:i:s', $date);

    $sql = "select U.id_user, O.online_date
      from `user` U
      join tbl_user_online O ON O.id_user=U.id_user      
      where online_date > '$date' AND status_login=1
      order by online_date desc";
    $result = self::find_by_sql($sql);

    $arr = array();
    foreach ($result as $row) {
      $arr[] = $row->id_user;
    }
    return $arr;
  }

  static function getUserChat() {
    $date = time() - (self::$limit_online * 60);
    $date = date('Y-m-d H:i:s', $date);

    $sql = "select U.id_user, O.online_date, 
          IF(online_date > '$date' AND status_login=1, 1, 0) AS is_online
      from `user` U
      join tbl_user_online O ON O.id_user=U.id_user      
      order by is_online desc, online_date desc, U.nama asc";
    $result = self::find_by_sql($sql);

    $arr = array();
    foreach ($result as $row) {
      $user = UserAR::getByID($row->id_user);
      $temp_arr = $user->toJsonArrayShort();
      $temp_arr['is_online'] = $row->is_online;
      $arr[] = $temp_arr;
    }
    return $arr;
  }

  static function getAll() {
    $member = UserAR::find('all', array(
                'conditions' => "",
                'order' => 'nama asc, username asc'
    ));
    return $member;
  }

  function getPhotoURL() {
    if ($this->user_foto) {
      return Page::$web_url . 'images/employee/' . $this->user_foto;
    }
  }

  function getLastActivity() {
    $result = ActStreamAR::find('last', array(
                'conditions' => "stream_by = '$this->id_user' ",
                'order' => 'date_stream desc'
    ));
    return $result;
  }

}


class AdisDetailARModel extends ActiveRecord\Model { // setting  
  static $table_name = 'mgartjuald'; // setting

}

?>