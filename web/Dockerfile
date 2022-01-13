FROM node:14

WORKDIR /app/web

COPY package*.json ./

RUN yarn install

COPY . .
RUN yarn build
CMD ["yarn","dev"]