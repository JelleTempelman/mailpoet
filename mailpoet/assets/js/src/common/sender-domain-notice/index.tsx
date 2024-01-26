import { escapeHTML } from '@wordpress/escape-html';
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';
import { extractEmailDomain } from 'common/functions';
import { InlineNotice } from 'common/notices/inline-notice';
import { SenderDomainNoticeBody } from './sender-domain-notice-body';
import { SenderActions } from './sender-domain-notice-actions';

export type SenderRestrictionsType = {
  lowerLimit: number;
  isNewUser: boolean;
  isEnforcementOfNewRestrictionsInEffect: boolean;
  alwaysRewrite?: boolean;
};

type SenderDomainInlineNoticeProps = {
  authorizeAction: (e) => void;
  emailAddress: string;
  subscribersCount: number;
  isFreeDomain: boolean;
  isPartiallyVerifiedDomain: boolean;
  senderRestrictions: SenderRestrictionsType;
};

function SenderEmailRewriteInfo({ emailAddress = '' }): JSX.Element {
  const rewrittenEmail = `${emailAddress.replace(
    '@',
    '=',
  )}@replies.sendingservice.net`;

  return (
    <p>
      {createInterpolateElement(
        __('Will be sent as: <rewrittenFromEmail/>', 'mailpoet'),
        {
          rewrittenFromEmail: <strong>{escapeHTML(rewrittenEmail)}</strong>,
        },
      )}
    </p>
  );
}

function SenderDomainInlineNotice({
  emailAddress,
  authorizeAction,
  subscribersCount,
  isFreeDomain,
  isPartiallyVerifiedDomain,
  senderRestrictions,
}: SenderDomainInlineNoticeProps) {
  let showRewrittenEmail = false;
  const showAuthorizeButton = !isFreeDomain;
  let isAlert = true;

  const emailAddressDomain = extractEmailDomain(emailAddress);

  const LOWER_LIMIT = senderRestrictions?.lowerLimit || 500;

  const isNewUser = senderRestrictions?.isNewUser ?? true;
  const isEnforcementOfNewRestrictionsInEffect =
    senderRestrictions?.isEnforcementOfNewRestrictionsInEffect ?? true;
  // TODO: Remove after the enforcement date has passed
  const onlyShowWarnings =
    !isNewUser && !isEnforcementOfNewRestrictionsInEffect;

  const isSmallSender = subscribersCount <= LOWER_LIMIT;

  if (
    isSmallSender ||
    isPartiallyVerifiedDomain ||
    senderRestrictions.alwaysRewrite ||
    onlyShowWarnings
  ) {
    isAlert = false;
  }

  if (
    (isSmallSender || senderRestrictions.alwaysRewrite) &&
    !isPartiallyVerifiedDomain
  ) {
    showRewrittenEmail = true;
  }

  return (
    <InlineNotice
      status={isAlert ? 'alert' : 'info'}
      topMessage={
        showRewrittenEmail ? (
          <SenderEmailRewriteInfo emailAddress={emailAddress} />
        ) : undefined
      }
      actions={
        <SenderActions
          showAuthorizeButton={showAuthorizeButton}
          authorizeAction={authorizeAction}
          isFreeDomain={isFreeDomain}
          isPartiallyVerifiedDomain={isPartiallyVerifiedDomain}
        />
      }
    >
      <SenderDomainNoticeBody
        emailAddressDomain={emailAddressDomain}
        isFreeDomain={isFreeDomain}
        isPartiallyVerifiedDomain={isPartiallyVerifiedDomain}
        isSmallSender={isSmallSender}
        onlyShowWarnings={onlyShowWarnings}
      />
    </InlineNotice>
  );
}

export { SenderDomainInlineNotice };
