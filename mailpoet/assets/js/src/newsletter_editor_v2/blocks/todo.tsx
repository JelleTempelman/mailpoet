import { BlockConfiguration, BlockEditProps } from '@wordpress/blocks';

export const name = 'mailpoet/todo';

type TodoAttributes = {
  originalBlock: string;
};

export const settings: BlockConfiguration = {
  title: 'Todo block',
  description: 'This block needs to be implemented',
  category: 'text',
  attributes: {
    originalBlock: {
      type: 'string',
      default: 'Not set',
    },
  },
  supports: {
    html: false,
    multiple: true,
  },
  edit: function Edit({
    attributes,
  }: BlockEditProps<TodoAttributes>): JSX.Element {
    return <p>Todo {attributes.originalBlock}</p>;
  },
  save() {
    return null;
  },
};
