# Backend Dockerfile
FROM node:18-alpine

# Install build tools for native modules
RUN apk add --no-cache python3 make g++

WORKDIR /app

COPY package*.json ./

# Install dependencies and rebuild native modules
RUN npm install && npm rebuild bcrypt --build-from-source

COPY . .

EXPOSE 5000

CMD ["npm", "start"]
