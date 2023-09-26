import { useEffect, useRef } from 'react';
import {
  Panel,
  PanelBody,
  RangeControl,
  SelectControl,
} from '@wordpress/components';
import { MailPoet } from 'mailpoet';
import { useDispatch, useSelect } from '@wordpress/data';
import { partial } from 'lodash';
import { HorizontalAlignment } from 'common/styles';

import { ColorGradientSettings } from 'form-editor/components/color-gradient-settings';
import { FontSizeSettings } from 'form-editor/components/font-size-settings';
import { ImageSettings } from 'form-editor/components/image-settings';
import { CloseButtonsSettings } from 'form-editor/components/close-button-settings';
import { formStyles as defaultFormStyles } from 'form-editor/store/defaults';
import { FontFamilySettings } from '../font-family-settings';
import { storeName } from '../../store';

type StylesSettingsPanelProps = {
  onToggle: PanelBody.Props['onToggle'];
  isOpened: boolean;
};

function StylesSettingsPanel({ onToggle, isOpened }: StylesSettingsPanelProps) {
  const { changeFormSettings } = useDispatch(storeName);
  const settings = useSelect(
    (select) => select(storeName).getFormSettings(),
    [],
  );
  const settingsRef = useRef(settings);
  useEffect(() => {
    settingsRef.current = settings;
  }, [settings]);

  const updateStyles = (property, value) => {
    const updated = { ...settingsRef.current };
    updated[property] = value ?? defaultFormStyles[property] ?? undefined;
    void changeFormSettings(updated);
    settingsRef.current = updated;
  };

  return (
    <Panel>
      <PanelBody
        title={MailPoet.I18n.t('formSettingsStyles')}
        opened={isOpened}
        onToggle={onToggle}
      >
        <div className="mailpoet-styles-settings">
          <ColorGradientSettings
            title={MailPoet.I18n.t('formSettingsColor')}
            settings={[
              {
                label: MailPoet.I18n.t('formSettingsStylesBackground'),
                colorValue: settings.backgroundColor,
                gradientValue: settings.gradient,
                onColorChange: partial(updateStyles, 'backgroundColor'),
                onGradientChange: partial(updateStyles, 'gradient'),
              },
              {
                label: MailPoet.I18n.t('formSettingsStylesFont'),
                colorValue: settings.fontColor,
                onColorChange: partial(updateStyles, 'fontColor'),
              },
              {
                label: MailPoet.I18n.t('formSettingsBorder'),
                colorValue: settings.borderColor,
                onColorChange: partial(updateStyles, 'borderColor'),
              },
            ]}
          />
          <ImageSettings
            name={MailPoet.I18n.t('formSettingsStylesBackgroundImage')}
            imageUrl={settings.backgroundImageUrl}
            onImageUrlChange={partial(updateStyles, 'backgroundImageUrl')}
            imageDisplay={settings.backgroundImageDisplay}
            onImageDisplayChange={partial(
              updateStyles,
              'backgroundImageDisplay',
            )}
          />
          <FontSizeSettings
            value={settings.fontSize}
            onChange={partial(updateStyles, 'fontSize')}
          />
          <FontFamilySettings
            name={MailPoet.I18n.t('formSettingsStylesFontFamily')}
            value={settings.fontFamily}
            onChange={partial(updateStyles, 'fontFamily')}
          />
          <RangeControl
            label={MailPoet.I18n.t('formSettingsInputPadding')}
            value={settings.inputPadding}
            min={0}
            max={30}
            allowReset
            onChange={partial(updateStyles, 'inputPadding')}
          />
          <RangeControl
            label={MailPoet.I18n.t('formSettingsBorderSize')}
            value={settings.borderSize !== undefined ? settings.borderSize : 0}
            min={0}
            max={10}
            allowReset
            onChange={partial(updateStyles, 'borderSize')}
            className="mailpoet-automation-styles-border-size"
          />
          <RangeControl
            label={MailPoet.I18n.t('formSettingsBorderRadius')}
            value={
              settings.borderRadius !== undefined ? settings.borderRadius : 0
            }
            min={0}
            max={40}
            allowReset
            onChange={partial(updateStyles, 'borderRadius')}
          />
          <SelectControl
            label={MailPoet.I18n.t('formSettingsAlignment')}
            onChange={partial(updateStyles, 'alignment')}
            options={[
              {
                value: HorizontalAlignment.Left,
                label: MailPoet.I18n.t('formSettingsAlignmentLeft'),
              },
              {
                value: HorizontalAlignment.Center,
                label: MailPoet.I18n.t('formSettingsAlignmentCenter'),
              },
              {
                value: HorizontalAlignment.Right,
                label: MailPoet.I18n.t('formSettingsAlignmentRight'),
              },
            ]}
            value={settings.alignment}
          />
          <RangeControl
            label={MailPoet.I18n.t('formSettingsFormPadding')}
            value={settings.formPadding}
            min={0}
            max={40}
            allowReset
            onChange={partial(updateStyles, 'formPadding')}
          />
          <ColorGradientSettings
            title={MailPoet.I18n.t('validationMessageColor')}
            settings={[
              {
                label: MailPoet.I18n.t('successValidationColorTitle'),
                colorValue: settings.successValidationColor,
                onColorChange: partial(updateStyles, 'successValidationColor'),
              },
              {
                label: MailPoet.I18n.t('errorValidationColorTitle'),
                colorValue: settings.errorValidationColor,
                onColorChange: partial(updateStyles, 'errorValidationColor'),
              },
            ]}
          />
          <CloseButtonsSettings
            name={MailPoet.I18n.t('closeButtonHeading')}
            value={settings.closeButton}
            onChange={partial(updateStyles, 'closeButton')}
          />
        </div>
      </PanelBody>
    </Panel>
  );
}

export { StylesSettingsPanel };
