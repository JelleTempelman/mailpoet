import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from 'react';
import { Grid } from 'common/grid';
import { Input, Select } from 'common';
import { MailPoet } from 'mailpoet';
import { WooCommerceFormItem, FilterProps } from '../../../types';
import { storeName } from '../../../store';

export function validateTotalSpent(formItems: WooCommerceFormItem): boolean {
  const totalSpentIsInvalid =
    !formItems.total_spent_amount ||
    !formItems.total_spent_days ||
    !formItems.total_spent_type;

  return !totalSpentIsInvalid;
}

export function TotalSpentFields({ filterIndex }: FilterProps): JSX.Element {
  const segment: WooCommerceFormItem = useSelect(
    (select) => select(storeName).getSegmentFilter(filterIndex),
    [filterIndex],
  );
  const { updateSegmentFilter, updateSegmentFilterFromEvent } =
    useDispatch(storeName);
  const wooCurrencySymbol: string = useSelect(
    (select) => select(storeName).getWooCommerceCurrencySymbol(),
    [],
  );
  useEffect(() => {
    if (segment.total_spent_type === undefined) {
      void updateSegmentFilter({ total_spent_type: '>' }, filterIndex);
    }
  }, [updateSegmentFilter, segment, filterIndex]);
  return (
    <>
      <Grid.CenteredRow>
        <Select
          key="select"
          value={segment.total_spent_type}
          onChange={(e): void => {
            void updateSegmentFilterFromEvent(
              'total_spent_type',
              filterIndex,
              e,
            );
          }}
          automationId="select-total-spent-type"
        >
          <option value="=">{MailPoet.I18n.t('equals')}</option>
          <option value="!=">{MailPoet.I18n.t('notEquals')}</option>
          <option value=">">{MailPoet.I18n.t('moreThan')}</option>
          <option value="<">{MailPoet.I18n.t('lessThan')}</option>
        </Select>
        <Input
          data-automation-id="input-total-spent-amount"
          type="number"
          min={0}
          step={0.01}
          value={segment.total_spent_amount || ''}
          placeholder={MailPoet.I18n.t('wooSpentAmount')}
          onChange={(e): void => {
            void updateSegmentFilterFromEvent(
              'total_spent_amount',
              filterIndex,
              e,
            );
          }}
        />
        <div>{wooCurrencySymbol}</div>
      </Grid.CenteredRow>
      <Grid.CenteredRow>
        <div>{MailPoet.I18n.t('inTheLast')}</div>
        <Input
          data-automation-id="input-total-spent-days"
          type="number"
          min={1}
          value={segment.total_spent_days || ''}
          placeholder={MailPoet.I18n.t('daysPlaceholder')}
          onChange={(e): void => {
            void updateSegmentFilterFromEvent(
              'total_spent_days',
              filterIndex,
              e,
            );
          }}
        />
        <div>{MailPoet.I18n.t('days')}</div>
      </Grid.CenteredRow>
    </>
  );
}