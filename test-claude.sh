#!/bin/bash

# Claude API Test Script
# This script fetches available models and lets you test one

# Color codes for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Claude API Test Script ===${NC}\n"

# Set your API key here
API_KEY="YOUR_API_KEY_HERE"

# Check if API key has been set
if [ "$API_KEY" = "YOUR_API_KEY_HERE" ]; then
    echo -e "${RED}Error: Please set your API key in the script${NC}"
    echo "Edit the script and replace YOUR_API_KEY_HERE with your actual API key"
    exit 1
fi

# Check if jq is installed
if ! command -v jq &> /dev/null; then
    echo -e "${RED}Error: 'jq' is required for this script${NC}"
    echo "Please install jq: sudo apt-get install jq (Ubuntu/Debian) or brew install jq (Mac)"
    exit 1
fi

echo -e "${GREEN}Fetching available models...${NC}\n"

# Fetch available models
models_response=$(curl -s https://api.anthropic.com/v1/models \
  --header "x-api-key: ${API_KEY}" \
  --header "anthropic-version: 2023-06-01")

# Extract model IDs (top 5)
model_list=$(echo "$models_response" | jq -r '.data[0:5] | .[].id')
models=()
while IFS= read -r line; do
    models+=("$line")
done <<< "$model_list"

# Check if we got models
if [ ${#models[@]} -eq 0 ]; then
    echo -e "${RED}Error: Could not fetch models. Check your API key.${NC}"
    echo -e "\n${BLUE}API Response:${NC}"
    echo "$models_response" | jq '.'
    exit 1
fi

# Display models
echo -e "${BLUE}Available Models:${NC}"
for i in "${!models[@]}"; do
    echo "$((i+1)). ${models[$i]}"
done

# Prompt user to choose
echo ""
read -p "Choose a model (1-5): " choice

# Validate input
if ! [[ "$choice" =~ ^[1-5]$ ]]; then
    echo -e "${RED}Invalid choice. Please enter a number between 1 and 5.${NC}"
    exit 1
fi

# Get selected model
selected_model="${models[$((choice-1))]}"
echo -e "\n${GREEN}Selected model: ${selected_model}${NC}"
echo -e "${GREEN}Sending test message to Claude API...${NC}\n"

# Make the API call with selected model
response=$(curl -s https://api.anthropic.com/v1/messages \
  --header "x-api-key: ${API_KEY}" \
  --header "anthropic-version: 2023-06-01" \
  --header "content-type: application/json" \
  --data "{
    \"model\": \"${selected_model}\",
    \"max_tokens\": 1024,
    \"messages\": [
      {
        \"role\": \"user\",
        \"content\": \"Hello, Claude! Please respond with a brief greeting and confirm the API is working.\"
      }
    ]
  }")

# Display response
echo -e "${BLUE}Response:${NC}"
echo "$response" | jq '.'

# Extract and display just the message content
echo -e "\n${GREEN}Claude's message:${NC}"
echo "$response" | jq -r '.content[0].text'

echo -e "\n${GREEN}Test complete!${NC}"
