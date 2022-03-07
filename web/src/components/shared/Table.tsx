import { Box, BoxProps, chakra } from "@chakra-ui/react"
import React, { ReactNode } from "react"

interface TableProps extends BoxProps {
	header: ReactNode
	children: ReactNode
}

export const Table = ({ header, children, ...rest }: TableProps) => {
	return (
		<Box flex={1} background="background.secondary" overflow="auto" rounded="md" {...rest}>
			<chakra.table
				w="full"
				sx={{
					"& th, td": {
						padding: "0.5rem 1rem"
					},
					tableLayout: "fixed"
				}}
			>
				<chakra.thead>
					<chakra.tr
						sx={{
							"& th": {
								position: "sticky",
								top: 0,
								zIndex: 1,
								backgroundColor: "background.third"
							}
						}}
					>
						{header}
					</chakra.tr>
				</chakra.thead>
				<chakra.tbody>{children}</chakra.tbody>
			</chakra.table>
		</Box>
	)
}
