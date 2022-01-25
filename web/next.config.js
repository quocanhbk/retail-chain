/** @type {import('next').NextConfig} */
module.exports = {
	reactStrictMode: true,
	async redirects() {
		return [
			{
				source: "/admin/manage",
				destination: "/admin/manage/branch",
				permanent: true
			},
			{
				source: "/",
				destination: "/main/sale/cart",
				permanent: true
			},
			{
				source: "/main",
				destination: "/main/sale/cart",
				permanent: true
			}
		]
	},
	images: {
		domains: ["http://localhost", "149.28.148.73"]
	}
}
