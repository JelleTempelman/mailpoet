import ReactDOM from 'react-dom';
import { BrowserRouter } from 'react-router-dom';
import { dispatch, select } from '@wordpress/data';
import { TopBarWithBeamer } from '../../../../common/top_bar/top_bar';
import { Notices } from '../../../listing/components/notices';
import { Header } from './components/header';
import { Overview } from './components/overview';
import { Tabs } from './components/tabs';
import { createStore, Section, storeName } from './store';
import { registerApiErrorHandler } from '../../../listing/api-error-handler';
import { initializeApi } from './api';

function Analytics(): JSX.Element {
  return (
    <div className="mailpoet-automation-analytics">
      <Header />
      <Overview />
      <Tabs />
    </div>
  );
}

function App(): JSX.Element {
  return (
    <BrowserRouter>
      <TopBarWithBeamer />
      <Notices />
      <Analytics />
    </BrowserRouter>
  );
}

function boot() {
  initializeApi();
  select(storeName)
    .getSections()
    .forEach((section: Section) => {
      dispatch(storeName).updateSection(section);
    });
}

window.addEventListener('DOMContentLoaded', () => {
  const root = document.getElementById('mailpoet_automation_analytics');
  if (!root) {
    return;
  }
  createStore();
  registerApiErrorHandler();
  boot();
  ReactDOM.render(<App />, root);
});
