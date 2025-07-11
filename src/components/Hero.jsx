import React from 'react';
import './Hero.css';
import { siteContents } from '../data/contents';

function Hero() {
  return (
    <section className="hero-section">
      <div className="hero-text">
        <p
          className="sub-heading"
          dangerouslySetInnerHTML={{ __html: siteContents.hero.subHeading }}
        />
        <h1>
          {siteContents.hero.mainHeading[0]}
          <span className="highlight">{siteContents.hero.mainHeading[1]}</span>
          <br />
          {siteContents.hero.mainHeading[2]}
          <span className="highlight">{siteContents.hero.mainHeading[3]}</span>
        </h1>
      </div>
      <img
        src="images/poll.webp"
        alt={siteContents.hero.pollImageAlt}
        className="hero-poll-image"
      />
    </section>
  );
}

export default Hero;