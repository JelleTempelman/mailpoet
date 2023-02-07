<?php declare(strict_types = 1);

namespace MailPoet\AdminPages\Pages;

use AutomateWoo\Fields\Field;
use AutomateWoo\Trigger;
use MailPoet\AdminPages\PageRenderer;
use MailPoet\Automation\Engine\Data\Automation;
use MailPoet\Automation\Engine\Hooks;
use MailPoet\Automation\Engine\Mappers\AutomationMapper;
use MailPoet\Automation\Engine\Registry;
use MailPoet\Automation\Engine\Storage\AutomationStorage;
use MailPoet\Form\AssetsController;
use MailPoet\WP\Functions as WPFunctions;
use MailPoet\WP\Notice as WPNotice;

class AutomationEditor {
  /** @var AssetsController */
  private $assetsController;

  /** @var AutomationMapper */
  private $automationMapper;

  /** @var AutomationStorage */
  private $automationStorage;

  /** @var PageRenderer */
  private $pageRenderer;

  /** @var Registry */
  private $registry;

  /** @var WPFunctions */
  private $wp;

  public function __construct(
    AssetsController $assetsController,
    AutomationMapper $automationMapper,
    AutomationStorage $automationStorage,
    PageRenderer $pageRenderer,
    Registry $registry,
    WPFunctions $wp
  ) {
    $this->assetsController = $assetsController;
    $this->automationMapper = $automationMapper;
    $this->automationStorage = $automationStorage;
    $this->pageRenderer = $pageRenderer;
    $this->registry = $registry;
    $this->wp = $wp;
  }

  public function render() {
    $this->assetsController->setupAutomationEditorDependencies();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    $this->wp->doAction(Hooks::EDITOR_BEFORE_LOAD, (int)$id);

    $automation = $id ? $this->automationStorage->getAutomation($id) : null;
    if (!$automation) {
      $notice = new WPNotice(
        WPNotice::TYPE_ERROR,
        __('Automation not found.', 'mailpoet')
      );
      $notice->displayWPNotice();
      $this->pageRenderer->displayPage('blank.html');
      return;
    }

    if ($automation->getStatus() === Automation::STATUS_TRASH) {
      $this->wp->wpSafeRedirect($this->wp->adminUrl('admin.php?page=mailpoet-automation&status=trash'));
      exit();
    }

    $automateWooTriggers = array_map(
      function(Trigger $trigger) : array {
        return [
          'key' => $trigger->get_name(),
          'name' => $trigger->get_title(),
          'description' => $trigger->get_description(),
          'fields' => array_map(
            function(Field $field) : array {
              return [
                'name' => $field->get_name(),
                'label' => $field->get_title(),
                'type' => $field->get_type(),
                'options' => method_exists($field, 'get_options') ? $field->get_options() : [],
                'description' => $field->get_description(),
                'placeholder' => $field->get_placeholder(),
                'required' => $field->get_required(),
              ];
            },
            $trigger->get_fields()),
        ];
      },
      $this->getAutomateWooTriggers()
    );

    $roles = new \WP_Roles();
    $this->pageRenderer->displayPage('automation/editor.html', [
      'registry' => $this->buildRegistry(),
      'context' => $this->buildContext(),
      'automation' => $this->automationMapper->buildAutomation($automation),
      'sub_menu' => 'mailpoet-automation',
      'locale_full' => $this->wp->getLocale(),
      'api' => [
        'root' => rtrim($this->wp->escUrlRaw($this->wp->restUrl()), '/'),
        'nonce' => $this->wp->wpCreateNonce('wp_rest'),
      ],
      'jsonapi' => [
        'root' => rtrim($this->wp->escUrlRaw(admin_url('admin-ajax.php')), '/'),
      ],
      'user_roles' => $roles->get_names(),
      'automateWooTriggers' => $automateWooTriggers,
    ]);
  }

  private function buildRegistry(): array {
    $steps = [];
    foreach ($this->registry->getSteps() as $key => $step) {
      $steps[$key] = [
        'key' => $step->getKey(),
        'name' => $step->getName(),
        'args_schema' => $step->getArgsSchema()->toArray(),
      ];
    }
    return ['steps' => $steps];
  }

  private function buildContext(): array {
    $data = [];
    foreach ($this->registry->getContextFactories() as $key => $factory) {
      $data[$key] = $factory();
    }
    return $data;
  }

  private function getAutomateWooTriggers() {
    $triggers = [];
    if (class_exists('AutomateWoo\Triggers')) {
      $triggers = \AutomateWoo\Triggers::get_all();
    }
    return $triggers;
  }
}
