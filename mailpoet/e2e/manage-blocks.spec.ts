import { test, expect } from '@playwright/test';
import { logIn } from './utils/login';

test.skip('can manage blocks inside the new email editor', async ({ page }) => {
	const newsletterTitle = `Newsletter-${new Date().getTime().toString()}`;
	
	await page.goto('https://mpperftesting.com/wp-admin/');

	// Log in with admin credentials
	await logIn(page, 'admin', 'u2YNOVMJVd@!');

	// Go to create a new newsletter page
	await page.goto('https://mpperftesting.com/wp-admin/admin.php?page=mailpoet-newsletters#/new');

	// Choose to create it using a new email editor
	await expect(page.getByText('What would you like to create?')).toBeVisible();
	await page.getByTestId('create_standard_email_dropdown').click();
	await page.getByText('Create using new editor (Beta)').click();
	await page.getByRole('button', { name: 'Continue', exact: true }).click();

	// Fill the newsletter title
	await page.getByTestId('email_subject').fill(newsletterTitle);

	// Add image block by writing in the email editor
	await page.getByLabel('Empty block; start writing or type forward slash to choose a block').fill('/imag');
	await page.getByRole('option', { name: 'Image' }).click();

	// Add Paragraph block via blocks search
	await page.getByLabel('Add', { exact: true }).click();
	await page.getByPlaceholder('Search', { exact: true }).fill('Paragraph');
	await page.getByLabel('Most used').getByRole('option', { name: 'Paragraph' }).click();

	// Add heading text to a Heading block and verify it
	await page.getByLabel('Block: Heading').fill('This is heading');
	await expect(page.getByText('This is heading')).toBeVisible();

	// Add button block via Add Block button
	await page.getByLabel('Close', {exact: true}).click(); // Close left sidebar
	await page.getByLabel('Empty block; start writing or type forward slash to choose a block').click(); // Choose place inside the editor
	await page.getByLabel('Add block').click(); // Open blocks popup to add a new block
	await page.getByRole('option', { name: 'Image' }).click(); // Add Image block

	// Change text color and background of a heading block
	await page.getByLabel('Block: Heading').click(); // Click to choose Heading block
	await page.getByLabel( 'Color Text styles' ).click(); // Open text styles
	await page.getByRole( 'option', {name: 'Color: Vivid purple',} ).click(); // Choose purple color
	await page.getByLabel( 'Color Text styles' ).click(); // Close text styles
	await expect(page.getByText('This is heading')).toHaveCSS('color', 'rgb(155, 81, 224)');

	// Save the newsletter as a draft
	await page.getByText('Save Draft').click();
	await expect(page.getByLabel('Dismiss this notice').getByText('Email saved!')).toBeVisible();
});
