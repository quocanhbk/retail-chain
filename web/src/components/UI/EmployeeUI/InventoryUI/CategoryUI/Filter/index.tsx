import { Box, Checkbox, CheckboxGroup, Heading, Stack, StackDivider, VStack } from "@chakra-ui/react"

const Filter = () => {
    return (
		<Box w="full" boxShadow="xs" p={4} rounded="md" bg="white">
			<Heading as="h5" size="sm" mb={4}>
				{"Nhà cung cấp"}
			</Heading>
			<CheckboxGroup colorScheme="green" defaultValue={["naruto", "kakashi"]}>
				<VStack divider={<StackDivider borderColor="gray.200" />} spacing={2} align="stretch">
					<Checkbox value="1">Nhà cung cấp chung</Checkbox>
					<Checkbox value="2">Nhà cung cấp 1</Checkbox>
					<Checkbox value="3">Nhà cung cấp 2</Checkbox>
				</VStack>
			</CheckboxGroup>
		</Box>
	)
}

export default Filter