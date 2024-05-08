// import { test, expect } from '@playwright/test';
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { logIn } from './utils/login';

test('can manage blocks inside the new email editor', async ({ page, editor }) => {
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

	// Insert columns block
	await editor.insertBlock( {
			name: 'core/columns',
		} );

	// Type Header block and customize it
	await page.getByLabel('Block: Heading').fill('This is heading');
	await page.getByLabel( 'Color Text styles' ).click(); // Open text styles
	await page
		.getByRole( 'option', {
			name: 'Color: Luminous vivid orange',
		} )
		.click();
	await page.getByLabel( 'Color Text styles' ).click(); // Close text styles
	// The below assertion does not work because it requires probably the exact values
	// await expect.poll( editor.getBlocks ).toContain( [
	// 	{
	// 		attributes: {
	// 			content: 'This is heading',
	// 		},
	// 	},
	// ] );

	// Show block toolbar and align it
	// (this will auto choose Heading block as the last touched)
	await editor.showBlockToolbar();
	const blockToolbar = page.locator(
		'role=toolbar[name="Block tools"i]'
	);
	const button = blockToolbar.locator( `role=button[name="Bold"]` );
	await button.click();

	// Select columns block
	await editor.selectBlocks(
		page.locator( 'role=document[name="Block: Columns"i]' )
	);

	// Click block options menu item
	await editor.clickBlockOptionsMenuItem('Duplicate');

	// Add blocks via Editor util
	await editor.insertBlock( {
			name: 'core/paragraph',
		} );
	await editor.insertBlock( {
			name: 'core/list',
		} );
	await editor.insertBlock( {
			name: 'core/buttons',
		} );

	// Save the newsletter as a draft
	await page.getByText('Save Draft').click();
	await expect(page.getByLabel('Dismiss this notice').getByText('Email saved!')).toBeVisible();
});
