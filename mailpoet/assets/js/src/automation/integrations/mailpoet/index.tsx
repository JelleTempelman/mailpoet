import { registerStepType } from '../../editor/store';
import { step as SendEmailStep } from './steps/send_email';
import { step as SomeoneSubscribesTrigger } from './steps/someone-subscribes';
import { step as WpUserRegisteredTrigger } from './steps/wp-user-registered';
import { step as AddTagsAction } from './steps/add_tags';
import { step as RemoveTagsAction } from './steps/remove_tags';
import { step as AddToListStep } from './steps/add_to_list';
import { registerStepControls } from './step-controls';

export const initialize = (): void => {
  registerStepType(SendEmailStep);
  registerStepType(WpUserRegisteredTrigger);
  registerStepType(SomeoneSubscribesTrigger);
  registerStepType(AddTagsAction);
  registerStepType(RemoveTagsAction);
  registerStepType(AddToListStep);
  registerStepControls();
};
