<?php declare(strict_types = 1);

namespace MailPoet\EmailEditor\Integration;

use MailPoet\EmailEditor\Core\EmailEditor as CoreEmailEditor;
use MailPoet\Entities\NewsletterEntity;
use MailPoet\Features\FeaturesController;
use MailPoet\Newsletter\NewslettersRepository;
use MailPoet\WP\Functions as WPFunctions;

class EmailEditor {
  const MAILPOET_EMAIL_POST_TYPE = 'mailpoet_email';

  /** @var \MailPoet\EmailEditor\Core\EmailEditor */
  private $coreEmailEditor;

  /** @var WPFunctions */
  private $wp;

  /** @var FeaturesController */
  private $featuresController;

  /** @var NewslettersRepository */
  private $newsletterRepository;

  public function __construct(
    CoreEmailEditor $coreEmailEditor,
    WPFunctions $wp,
    FeaturesController $featuresController,
    NewslettersRepository $newsletterRepository
  ) {
    $this->coreEmailEditor = $coreEmailEditor;
    $this->wp = $wp;
    $this->featuresController = $featuresController;
    $this->newsletterRepository = $newsletterRepository;
  }

  public function initialize(): void {
    if (!$this->featuresController->isSupported(FeaturesController::GUTENBERG_EMAIL_EDITOR)) {
      return;
    }
    $this->wp->addFilter('mailpoet_email_editor_post_types', [$this, 'addEmailPostType']);
    $this->wp->addFilter('save_post', [$this, 'onEmailSave'], 10, 2);
    $this->coreEmailEditor->initialize();
  }

  public function addEmailPostType(array $postTypes): array {
    $postTypes[] = [
      'name' => self::MAILPOET_EMAIL_POST_TYPE,
      'args' => [
        'labels' => [
          'name' => __('Emails', 'mailpoet'),
          'singular_name' => __('Email', 'mailpoet'),
        ],
        'rewrite' => ['slug' => self::MAILPOET_EMAIL_POST_TYPE],
      ],
    ];
    return $postTypes;
  }

  /**
   * This method ensures that saved email has an associated newsletter entity.
   * In the future we will also need to save additional parameters like subject, type, etc.
   */
  public function onEmailSave($postId, \WP_Post $post): void {
    if ($post->post_type !== self::MAILPOET_EMAIL_POST_TYPE) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
      return;
    }
    $newsletter = $this->newsletterRepository->findOneBy(['wpPostId' => $postId]);
    if ($newsletter) {
      return;
    }
    $newsletter = new NewsletterEntity();
    $newsletter->setWpPostId($postId);
    $newsletter->setSubject('New Editor Email ' . $postId);
    $newsletter->setType(NewsletterEntity::TYPE_STANDARD); // We allow only standard emails in the new editor for now
    $this->newsletterRepository->persist($newsletter);
    $this->newsletterRepository->flush();
  }
}
