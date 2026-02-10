#!/bin/bash

# 1. Получить токен
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"manager@example.com","password":"PassPass1"}' \
  | grep -o '"token":"[^"]*' | cut -d'"' -f4)

# 2. Использовать токен
curl -X GET http://localhost:8000/api/tickets/statistics \
  -H "Authorization: Bearer $TOKEN"