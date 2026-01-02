# Frontend Dockerfile
FROM node:18-alpine

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .

# Build step if we were doing production, but for dev failover we use vite dev
EXPOSE 5173

CMD ["npm", "run", "dev"]
