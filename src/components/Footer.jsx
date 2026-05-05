// src/components/Footer.jsx

import React from 'react';
import './Footer.css';
import { siteContents } from '../data/contents';

function Footer() {
  const noticeItems = siteContents.footerContent.centeredNotice.split('|').map(item => item.trim());
  const agencyInfoItems = siteContents.footerContent.agencyInfo.split('|').map(item => item.trim());

  return (
    <footer>
      <div className="footer-content-area">
        <div className="footer-main-content">
          <div className="footer-logo">
            <img src="images/logo.webp" alt={siteContents.footerContent.logoAlt} />
          </div>
          <div className="footer-info">
            <p><strong>{siteContents.footerContent.siteName}</strong></p>
            <p>{siteContents.footerContent.address}</p>
            <p className="footer-split-text">
              {agencyInfoItems.map((item, index) => (
                <span key={index} className="split-item">{item}</span>
              ))}
            </p>
          </div>
        </div>
        <p className="footer-split-text footer-centered-notice">
          {noticeItems.map((item, index) => (
            <span key={index} className="split-item">{item}</span>
          ))}
        </p>
      </div>

      <div className="footer-legal">
        <p>{siteContents.footer.notice1}</p>
        <p>{siteContents.footer.notice2}</p>
        <p className="copyright">{siteContents.footer.copyright}</p>
      </div>
    </footer>
  );
}

export default Footer;
