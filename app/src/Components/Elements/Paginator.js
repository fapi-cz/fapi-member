import React, {useEffect} from 'react';
import Select from "Components/Elements/Select";
import arrowForward from 'Images/arrow-forward.svg';
import arrowBackward from 'Images/arrow-backward.svg';

function Paginator({page, setPage, itemsPerPage, setItemsPerPage, itemCount}) {

	const changePage = (direction) => {
		if (page + direction > 0 && (page + direction) * itemsPerPage - itemsPerPage < itemCount) {
			setPage(page + direction);
		}
	}

	useEffect(() => {
		if (page * itemsPerPage > itemCount) {
			setPage(1);
		}

	}, [itemCount, itemsPerPage]);

    return (
		<div
			className='paginator'
		>
			<div style={{width: '145px'}}/>
			<span className='paginator-controls'>
				<img
					className='clickable-icon paginator-arrow'
					src={arrowBackward}
					onClick={() => {changePage(-1)}}
				/>
				<span>
					{page * itemsPerPage - itemsPerPage + (itemCount > 0 ? 1 : 0)} až {page * itemsPerPage < itemCount ? page * itemsPerPage : itemCount} z {itemCount}
				</span>
				<img
					className='clickable-icon paginator-arrow'
					src={arrowForward}
					onClick={() => {changePage(1)}}
				/>
			</span>
			<span>
				<span>Stránkování: </span>
				<Select
				id={'paginator-items-per-page'}
				options={[
					{value: 25, text: '25'},
					{value: 50, text: '50'},
					{value: 100, text: '100'},
				]}
				defaultValue={5}
				onChangeUpdateFunction={setItemsPerPage}
				includeEmptyOption={false}
			/>
			</span>

		</div>
    );
}

export default Paginator;
