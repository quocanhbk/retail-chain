// @next/next/no-document-import-in-page
import Document, { Html, Head, Main, NextScript } from "next/document"

class MyDocument extends Document {
	render() {
		return (
			<Html lang="en">
				<Head>
					<title>Chain Store</title>
				</Head>
				<body>
					<Main />
					<NextScript />
				</body>
			</Html>
		)
	}
}

export default MyDocument
