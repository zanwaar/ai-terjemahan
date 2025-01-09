# AI Translation Service

A Laravel-based application that integrates multiple AI services for translation purposes.

## Supported AI Services

- OpenAI (GPT-3.5 Turbo)
- Google Gemini Pro
- Groq

## Setup

1. Clone the repository
2. Install dependencies:
```bash
composer install# AI Translation Service

A Laravel-based application that integrates multiple AI services for translation purposes.

## Supported AI Services

- OpenAI (GPT-3.5 Turbo)
- Google Gemini Pro 
- Groq

## Architecture

### 1. Web Interface
- Route: `/translate`
- Views: Translation form and results display
- Real-time translation status updates

### 2. Job System
- Queue-based processing
- Handles long-running translations
- Automatic failover between services
- Background job processing with Redis

### 3. AI Services Integration
- OpenAI Service
- Gemini Service
- Groq Service
- Fallback mechanisms

## Setup

1. Clone the repository:
```bash
git clone https://github.com/yourusername/ai-translation.git
cd ai-translation