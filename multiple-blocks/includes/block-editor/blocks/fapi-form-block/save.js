import { useBlockProps } from '@wordpress/block-editor';

export default function save( props ) {
	const blockProps = useBlockProps.save();

	return (
		<script
			type="text/javascript"
			src={
				'https://form.fapi.cz/script.php?id=' + props.attributes.path
			}
			{ ...blockProps }
			path={ props.attributes.path }
		></script>
	);
}
