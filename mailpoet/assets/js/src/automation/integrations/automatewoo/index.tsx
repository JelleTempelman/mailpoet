import { registerStepType } from '../../editor/store';
import { step as GeneralTrigger } from './steps/general_trigger';

export const initialize = (): void => {
  registerStepType(GeneralTrigger);
};
