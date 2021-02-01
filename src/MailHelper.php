<?php
namespace Drupal\dyniva_mail;

use Drupal\Component\Render\FormattableMarkup;

class MailHelper {

  /**
   * @return array
   */
  public static function getMailConfigs() {
    static $configs;
    if (!isset($configs)) {
      $configs = [];
      foreach (\Drupal::service('config.factory')
                 ->getEditable('dyniva_mail.mailconfig')
                 ->get('mail') as $conf) {
        $configs[$conf['id']] = $conf;
      }
    }
    return $configs;
  }

  /**
   * @param $id
   * @param $args
   *
   * @return bool|mixed
   */
  public static function getMailTemplate($id, $args = []) {
    $configs = self::getMailConfigs();
    if (isset($configs[$id])) {

      if (empty($args)) {
        return $configs[$id];
      }

      foreach($configs[$id] as $field => $value) {
        // continue id.
        if($field == 'id') {
          continue;
        }
        // re title and body field.
        $configs[$id][$field] = (string)new FormattableMarkup($value, $args);
      }

      return $configs[$id];
    }
    return FALSE;
  }

  /**
   * $res = \Drupal\adc_mail\MailHelper::getMailConfigs();
   * \Drupal\dyniva_mail\MailHelper::sendMail('244705779@qq.com', 'user_register', ['
   *
   * @param       $to
   * @param       $mail_id
   * @param array $args
   *
   * @param array $params
   *
   * @return mixed
   * @throws \Exception
   */
  public static function sendMail($to, $mail_id, $args = [], $params = []) {
    $mail_info = self::getMailTemplate($mail_id, $args);
    if (!$mail_info) {
      throw new \Exception(t('Template not found: @id', ['@id' => $mail_id]));
    }
    if (is_array($to)) {
      $to = implode(',', $to);
    }
    \Drupal::logger('debug_mail')->notice( "<pre>" . print_r([
        'to'=> $to,
        'info'=> $mail_info,
        'params' => $params
      ], 1));
    return dyniva_sendmail($to, $mail_info['title'], $mail_info['body'], $params);
  }

  /**
   * @param $to
   * @param $mail_id
   * @param $args
   *
   * @return bool
   */
  public static function sendMailQueue($to, $mail_id, $args = [], $params = []) {

    $mail_info = self::getMailTemplate($mail_id, $args);

    if (is_array($to)) {
      //Filtering to
      $to = implode(',', $to);
    }
    return dyniva_sendmail_queue($to, $mail_info['title'], $mail_info['body'], $params);
  }
}
