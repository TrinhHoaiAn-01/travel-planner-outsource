import React from 'react';
import { createRoot } from 'react-dom/client';
import Page from './storefront/Page';
import Boundary from './Boundary';
import './storefront/style.css';

createRoot(document.getElementById('commerce-root')!).render(<Boundary><Page /></Boundary>);
