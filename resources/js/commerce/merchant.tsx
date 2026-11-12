import React from 'react';
import { createRoot } from 'react-dom/client';
import Page from './merchant/Page';
import Boundary from './Boundary';
import './merchant/style.css';

createRoot(document.getElementById('commerce-root')!).render(<Boundary><Page /></Boundary>);
