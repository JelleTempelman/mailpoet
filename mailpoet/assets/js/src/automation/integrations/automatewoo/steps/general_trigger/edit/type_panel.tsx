import { PanelBody, SelectControl } from '@wordpress/components';
import { PlainBodyTitle } from '../../../../../editor/components';
import { __ } from '@wordpress/i18n';
import { dispatch, useSelect } from '@wordpress/data';
import { storeName } from '../../../../../editor/store';
//@ts-ignore
const triggers = window.mailpoet_automate_woo_triggers || [];
console.log({triggers});

//@ToDo: Load the options dynamically from AutomateWoo
const options = Object.values(triggers).map(
  (trigger:any) => ({ label: trigger.name, value: trigger.key })
)

export function TypePanel(): JSX.Element {
  const { selectedStep } = useSelect((select) => {
    return {
      selectedStep: select(storeName).getSelectedStep(),
    };
  });

  const selected = selectedStep.args?.aw_trigger as string | 'order_note_added';
  console.log({selected, triggers});
  const selectedObject = selected ? triggers[selected].fields || {} : {};
  const update = (value: string) => {
    dispatch(storeName).updateStepArgs(selectedStep.id, 'aw_trigger', value);
  };

  return (
    <PanelBody opened>
      <PlainBodyTitle title={__('AutomateWoo Trigger', 'mailpoet')} />

      <SelectControl options={options} value={selected} onChange={update} />
      <pre>{JSON.stringify(selectedObject, null, 2)}</pre>
    </PanelBody>
  );
}
