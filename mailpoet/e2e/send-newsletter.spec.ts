import { test, expect } from '@playwright/test';
import { logIn } from './utils/login';

test.skip('can create and send newsletter', async ({ page }) => {
	const newsletterTitle = `Newsletter-${new Date().getTime().toString()}`;
	
	await page.goto('https://mpperftesting.com/wp-admin/');
	
	// Log in with admin credentials
	await logIn(page, 'admin', 'u2YNOVMJVd@!');

	// Go to create a new newsletter page
	await page.goto('https://mpperftesting.com/wp-admin/admin.php?page=mailpoet-newsletters#/new');
	
	// Choose to create it using a new email editor
	await expect(page.getByRole('heading', { name: 'What would you like to create?' })).toBeVisible();
	await page.getByTestId('create_standard_email_dropdown').click();
	await page.getByRole('menuitem', { name: 'Create using new editor (Beta)' }).click();
	await page
		.getByRole('button', { name: 'Continue', exact: true })
		.click();

	// Fill the newsletter title
	await page.getByTestId('email_subject').fill(newsletterTitle);

	// Save the newsletter as a draft and then proceed it further
	await page
		.getByRole('button', { name: 'Save Draft', exact: true })
		.click();
	await expect(page.getByLabel('Dismiss this notice')).toContainText(
		'Email saved!'
	);
	await page
		.getByRole('button', { name: 'Send', exact: true })
		.click();
	await expect(page.getByRole('heading', { name: 'New Email' })).toBeVisible();

	// Change the newsletter title
	await page.getByPlaceholder('Type newsletter subject').fill(newsletterTitle);

	// Choose default list
	await page.selectOption('#mailpoet_segments', 'Newsletter mailing list');

	// Send the newsletter
	await page.getByTestId('email-submit').click();

	await expect(
		page.getByText('The newsletter is being sent...')
	).toBeVisible();
});
