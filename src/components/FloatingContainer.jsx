// src/components/FloatingContainer.jsx

import React, { useState } from 'react';
import { FaCommentDots, FaBell } from 'react-icons/fa';
import './FloatingContainer.css';
import { siteContents } from '../data/contents';

function FloatingContainer({ onShowPopup }) {
  const [isSmsExpanded, setIsSmsExpanded] = useState(false);
  const [smsMessage, setSmsMessage] = useState('');
  const [smsPhone, setSmsPhone] = useState('');
  const [isSmsSubmitting, setIsSmsSubmitting] = useState(false);
  const [smsSubmitMessage, setSmsSubmitMessage] = useState('');

  const handleSmsToggle = () => {
    if (!isSmsExpanded) {
      setSmsSubmitMessage('');
      setSmsMessage('');
      setSmsPhone('');
    }
    setIsSmsExpanded(!isSmsExpanded);
  };

  const handleSmsSubmit = async (event) => {
    event.preventDefault();

    if (!smsMessage.trim()) {
      alert(siteContents.floating.sms.alerts.messageRequired);
      return;
    }
    if (!smsPhone.trim()) {
      alert(siteContents.floating.sms.alerts.phoneRequired);
      return;
    }

    setIsSmsSubmitting(true);
    setSmsSubmitMessage('');

    const formData = new FormData();
    formData.append('action', 'sms');
    formData.append('Message', smsMessage);
    formData.append('Mobile', smsPhone);

    const phpApiUrl = 'send_sms.php';

    try {
      const response = await fetch(phpApiUrl, {
        method: 'POST',
        body: formData,
      });
      const result = await response.json();
      setSmsSubmitMessage(result.message);

      if (result.success) {
        setSmsMessage('');
        setSmsPhone('');
        setTimeout(() => {
          setIsSmsExpanded(false);
        }, 2000);
      }
    } catch (error) {
      setSmsSubmitMessage(siteContents.contact.alerts.networkError);
      console.error('Fetch Error:', error);
    } finally {
      setIsSmsSubmitting(false);
    }
  };

  return (
    <>
      <div className="floating-button-container">
        <a href={`tel:${siteContents.floating.callNumber}`} className="floating-call-button call">
          <span>{siteContents.floating.call}</span>
          <span className="call-number">
            <img src="images/phone.png" alt={siteContents.floating.callIconAlt} className="call-icon" />
            {siteContents.floating.callNumber}
          </span>
        </a>
        <div className="bottom-button-row">
          <a href="#contact" className="floating-call-button reservation">
            <span>{siteContents.floating.reservation}</span>
          </a>
          <button className="floating-call-button popup-show" onClick={onShowPopup}>
            <FaBell />
            <span>{siteContents.floating.showPopup}</span>
          </button>
        </div>
      </div>

      <div className={`sms-widget-container ${isSmsExpanded ? 'expanded' : ''}`}>
        {isSmsExpanded ? (
          <div className="sms-form-wrapper">
            <div className="sms-form-header">
              <h3>{siteContents.floating.sms.title}</h3>
              <button onClick={handleSmsToggle} className="sms-close-button">&times;</button>
            </div>
            <form onSubmit={handleSmsSubmit} className="sms-form-content">
              <div className="sms-form-group">
                <label htmlFor="sms-message">{siteContents.floating.sms.messageLabel}</label>
                <textarea
                  id="sms-message"
                  value={smsMessage}
                  onChange={(e) => setSmsMessage(e.target.value)}
                  placeholder={siteContents.floating.sms.messagePlaceholder}
                  disabled={isSmsSubmitting}
                />
              </div>
              <div className="sms-form-group">
                <label htmlFor="sms-phone">{siteContents.floating.sms.phoneLabel}</label>
                <input
                  type="tel"
                  id="sms-phone"
                  value={smsPhone}
                  onChange={(e) => setSmsPhone(e.target.value)}
                  placeholder={siteContents.floating.sms.phonePlaceholder}
                  disabled={isSmsSubmitting}
                />
              </div>
              <button type="submit" className="sms-submit-button" disabled={isSmsSubmitting}>
                {isSmsSubmitting ? siteContents.floating.sms.submittingButton : siteContents.floating.sms.submitButton}
              </button>
              {smsSubmitMessage && <p className="sms-submit-message">{smsSubmitMessage}</p>}
            </form>
          </div>
        ) : (
          <button className="sms-icon-button" onClick={handleSmsToggle}>
            <FaCommentDots size={24} />
            <span>{siteContents.floating.sms.toggleButton}</span>
          </button>
        )}
      </div>
    </>
  );
}

export default FloatingContainer;
