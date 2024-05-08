<?php declare(strict_types = 1);

namespace MailPoet\Test\Acceptance;

class ManageBlocksCest {
  public function manageBlocksInsideNewEmailEditor(\AcceptanceTester $i) {
    $newsletterTitle = 'Newsletter' . time();
    
    $i->amOnPage('/wp-admin/');

    // Log in with admin credentials
    $i->logging();

    // Go to create a new newsletter page
    $i->amOnPage('/wp-admin/admin.php?page=mailpoet-newsletters#/new');

    // Choose to create it using a new email editor
    $i->waitForText('What would you like to create?');
    $i->click('[data-automation-id="create_standard_email_dropdown"]');
    $i->click('Create using new editor (Beta)');
    $i->click('Continue');
    $i->waitForElement('[aria-label="Block: Heading"]'); // To wait before filling the title

    // Fill the newsletter title
    $i->fillField('[data-automation-id="email_subject"]', $newsletterTitle);

    // Add image block by writing in the email editor
    $i->fillField('[aria-label="Empty block; start writing or type forward slash to choose a block"]', '/imag');
    $i->click('Image');

    // Add Paragraph block via blocks search
    $i->click('[aria-label="Add"]');
    $i->fillField('[placeholder="Search"]', 'Paragraph');
    $i->click('Paragraph');

    // Add heading text to a Heading block and verify it
    $i->fillField('[aria-label="Block: Heading"]', 'This is heading');
    $i->see('This is heading');

    // Add button block via Add Block button
    $i->click('[aria-label="Close"]'); // Close left sidebar
    $i->click('[aria-label="Empty block; start writing or type forward slash to choose a block"]'); // Choose place inside the editor
    $i->click('[aria-label="Add block"]'); // Open blocks popup to add a new block
    $i->click('Image'); // Add Image block

    // Change text color and background of a heading block
    $i->click('[aria-label="Block: Heading"]'); // Click to choose Heading block
    $i->click('[aria-label="Color Text styles"]'); // Open text styles
    $i->click('[aria-label="Color: Vivid purple"]'); // Choose purple color
    $i->click('[aria-label="Color Text styles"]'); // Close text styles
    $i->assertCssProperty('[aria-label="Block: Heading"]', 'color', 'rgba(155, 81, 224, 1)');

    // Save the newsletter as a draft
    $i->click('Save Draft');
    $i->waitForText('Email saved!');
  }
}
