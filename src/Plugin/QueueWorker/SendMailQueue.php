<?php

/**
 * @file
 * Contains \Drupal\adc_mail\Plugin\QueueWorker\NewsQueue.
 */
namespace Drupal\dyniva_mail\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
/**
 * Processes Tasks for Learning.
 *
 * @QueueWorker(
 *   id = "send_mail_queue",
 *   title = @Translation("Send mail."),
 *   cron = {"time" = 60}
 * )
 */
class SendMailQueue extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    \Drupal::logger('dyniva_mail')->info("Queue run: \r\n" . print_r($data, true));
    \Drupal::logger('dyniva_mail')->info("send mail to: " . $data['to']);
    $from = \Drupal::config('system.site')->get('mail');

    $params = $data['params'];
    $params['subject'] = $data['subject'];
    $params['body'] = $data['body'];
    $langcode = \Drupal::languageManager()->getDefaultLanguage();

    // Send email with drupal_mail.
    return \Drupal::service('plugin.manager.mail')->mail('dyniva_mail', 'dyniva_mail', $data['to'], $langcode, $params, $from);
  }
}
