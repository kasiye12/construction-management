#!/bin/bash

echo "🏗️ Setting up Professional Laravel Architecture..."
echo "=================================================="

# 1. Create Directory Structure
mkdir -p app/{Services,Repositories,Contracts,Traits,Helpers,Enums,DTOs}
mkdir -p app/Http/{Controllers/{Admin,Api},Requests,Middleware,Resources}
mkdir -p app/Models/{Construction,Finance,User}
