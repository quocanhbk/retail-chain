import { Stack, RadioGroup, Radio } from "@chakra-ui/react"

interface RadioData {
	id: string
	value: string
}

export interface RadioControlProps {
	value: string
	onChange?: (nextValue: string) => void
	data: RadioData[]
}

export const RadioControl = ({ value, onChange, data }: RadioControlProps) => {
	return (
		<RadioGroup onChange={onChange} value={value}>
			<Stack
				direction="row"
				spacing={8}
				justify="space-around"
				border="1px"
				borderColor="gray.200"
				rounded="lg"
				h="2.5rem"
				background="white"
			>
				{data.map(item => (
					<Radio key={item.value} value={item.id}>
						{item.value}
					</Radio>
				))}
			</Stack>
		</RadioGroup>
	)
}

export default RadioControl
