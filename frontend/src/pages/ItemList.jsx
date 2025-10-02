import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';

export default function ItemList() {
	const [items, setItems] = useState([]);

	useEffect(() => {
		axios.get('http://localhost/api/items').then((res) => {
			setItems(res.data);
		});
	}, []);

	return (
		<div>
			<h1>商品一覧</h1>
			<ul>
				{items.map((item) => (
					<li key={item.id}>
						<Link to={`/items/${item.id}`}>
							{item.name} - ￥{item.price}
						</Link>
					</li>
				))}
			</ul>
		</div>
	);
}
