/** @type {import('next').NextConfig} */
module.exports = {
	reactStrictMode: true,
	async redirects() {
		return [
			{
				source: "/",
				destination: "/login",
				permanent: true,
			},
		]
	},
	images: {
		domains: ["http://localhost"],
	},
}
