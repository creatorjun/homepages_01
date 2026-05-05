// src/App.jsx

import React, { useState, useEffect, useCallback } from 'react';
import AOS from 'aos';
import 'aos/dist/aos.css';
import './App.css';

import Header from './components/Header';
import Hero from './components/Hero';
import Premium from './components/Premium';
import Location from './components/Location';
import Flats from './components/Flats';
import Gallery from './components/Gallery';
import Contact from './components/Contact';
import Footer from './components/Footer';
import Popup from './components/Popup';
import FloatingContainer from './components/FloatingContainer';

import { siteContents } from './data/contents.jsx';

const getCookie = (name) => {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
};

function App() {
  const [isPopupVisible, setIsPopupVisible] = useState(false);
  const [isPopupClosing, setIsPopupClosing] = useState(false);

  useEffect(() => {
    AOS.init({
      duration: 1000,
      once: true,
    });

    if (getCookie('popup_closed_today') !== 'true') {
      setIsPopupVisible(true);
    }
  }, []);

  const handlePopupOpen = () => {
    if (isPopupVisible) return;
    setIsPopupClosing(false);
    setIsPopupVisible(true);
  };

  const handlePopupClose = useCallback(() => {
    if (isPopupClosing) return;

    setIsPopupClosing(true);
    setTimeout(() => {
      setIsPopupVisible(false);
      setIsPopupClosing(false);
    }, 500);
  }, [isPopupClosing]);

  return (
    <>
      <title>{siteContents.meta.title}</title>
      <meta name="description" content={siteContents.meta.description} />
      <meta property="og:title" content={siteContents.meta.ogTitle} />
      <meta property="og:description" content={siteContents.meta.ogDescription} />
      <meta property="og:image" content="images/og_image.jpg" />
      <meta property="og:url" content={siteContents.meta.ogUrl} />
      <meta property="og:type" content="website" />

      <Header />
      <main>
        <Hero />
        <Premium />
        <Location />
        <Flats />
        <Gallery />
        <Contact />
      </main>
      <Footer />
      <FloatingContainer onShowPopup={handlePopupOpen} />
      <Popup
        isVisible={isPopupVisible}
        isClosing={isPopupClosing}
        onClose={handlePopupClose}
      />
    </>
  );
}

export default App;
