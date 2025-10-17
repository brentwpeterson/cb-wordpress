<?php
/**
 * Template Name: AIO AEO GEO PDP Search Readiness Landing Page
 *
 * Custom landing page template for AIO AEO GEO PDP Search readiness
 */

get_header(); ?>

<style>
/* Custom CSS for AIO Search Readiness Landing Page */
.aio-landing-page {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.1);
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.5rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

.hero-description {
    font-size: 1.2rem;
    margin-bottom: 40px;
    opacity: 0.8;
}

.cta-button {
    display: inline-block;
    background: #ff6b6b;
    color: white;
    padding: 18px 40px;
    font-size: 1.2rem;
    font-weight: 600;
    text-decoration: none;
    border-radius: 50px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
}

.cta-button:hover {
    background: #ff5252;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
    color: white;
    text-decoration: none;
}

.features-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.features-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.section-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: #333;
}

.section-subtitle {
    text-align: center;
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 60px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 40px;
    margin-bottom: 60px;
}

.feature-card {
    background: white;
    padding: 40px 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 25px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    font-weight: bold;
}

.feature-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.feature-description {
    color: #666;
    line-height: 1.6;
}

.benefits-section {
    padding: 80px 0;
    background: white;
}

.benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 20px;
}

.benefit-item {
    display: flex;
    align-items: flex-start;
    gap: 20px;
}

.benefit-icon {
    width: 50px;
    height: 50px;
    background: #4caf50;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    flex-shrink: 0;
}

.benefit-content h3 {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.benefit-content p {
    color: #666;
    line-height: 1.6;
}

.cta-section {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
}

.cta-content {
    max-width: 600px;
    margin: 0 auto;
    padding: 0 20px;
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.cta-description {
    font-size: 1.2rem;
    margin-bottom: 40px;
    opacity: 0.9;
}

.cta-button-white {
    display: inline-block;
    background: white;
    color: #ff6b6b;
    padding: 18px 40px;
    font-size: 1.2rem;
    font-weight: 600;
    text-decoration: none;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.cta-button-white:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    text-decoration: none;
    color: #ff6b6b;
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }

    .hero-subtitle {
        font-size: 1.2rem;
    }

    .section-title {
        font-size: 2rem;
    }

    .features-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="aio-landing-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">AIO AEO GEO PDP<br>Search Readiness</h1>
            <p class="hero-subtitle">All-In-One Solution for Complete Search Optimization</p>
            <p class="hero-description">
                Transform your digital presence with comprehensive Answer Engine Optimization,
                Geographic targeting, and Product Detail Page optimization in one powerful package.
            </p>
            <a href="#contact" class="cta-button">Get Search Ready Now</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="features-container">
            <h2 class="section-title">Complete Search Optimization Suite</h2>
            <p class="section-subtitle">
                Our comprehensive approach covers all aspects of modern search optimization,
                ensuring your business is found by the right customers at the right time.
            </p>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">AIO</div>
                    <h3 class="feature-title">All-In-One Platform</h3>
                    <p class="feature-description">
                        Unified dashboard managing all your search optimization needs.
                        No more juggling multiple tools - everything you need in one place.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">AEO</div>
                    <h3 class="feature-title">Answer Engine Optimization</h3>
                    <p class="feature-description">
                        Optimize for AI-powered search engines and voice assistants.
                        Position your content to be the preferred answer for customer queries.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">GEO</div>
                    <h3 class="feature-title">Geographic Targeting</h3>
                    <p class="feature-description">
                        Advanced local SEO and geographic optimization.
                        Dominate local search results and connect with nearby customers.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">PDP</div>
                    <h3 class="feature-title">Product Detail Optimization</h3>
                    <p class="feature-description">
                        Maximize product page performance and conversion rates.
                        Optimize every element for both search engines and user experience.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="features-container">
            <h2 class="section-title">Why Choose Our Search Readiness Solution?</h2>
            <p class="section-subtitle">
                Experience measurable results with our proven optimization strategies
            </p>

            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon">↗</div>
                    <div class="benefit-content">
                        <h3>Increased Visibility</h3>
                        <p>Improve search rankings across all major search engines and AI platforms</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">🎯</div>
                    <div class="benefit-content">
                        <h3>Targeted Traffic</h3>
                        <p>Attract qualified leads who are actively searching for your products or services</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">💰</div>
                    <div class="benefit-content">
                        <h3>Higher Conversions</h3>
                        <p>Optimized pages that turn visitors into customers with improved user experience</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">📊</div>
                    <div class="benefit-content">
                        <h3>Data-Driven Results</h3>
                        <p>Comprehensive analytics and reporting to track your optimization success</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">🚀</div>
                    <div class="benefit-content">
                        <h3>Future-Ready</h3>
                        <p>Stay ahead of search algorithm changes with cutting-edge optimization techniques</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">⚡</div>
                    <div class="benefit-content">
                        <h3>Fast Implementation</h3>
                        <p>Quick setup and deployment with immediate impact on your search performance</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="cta-section" id="contact">
        <div class="cta-content">
            <h2 class="cta-title">Ready to Dominate Search Results?</h2>
            <p class="cta-description">
                Join hundreds of businesses that have transformed their online presence
                with our comprehensive search optimization solution.
            </p>
            <a href="mailto:contact@contentcucumber.local" class="cta-button-white">Start Your Optimization Journey</a>
        </div>
    </section>
</div>

<?php get_footer(); ?>