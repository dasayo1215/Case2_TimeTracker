import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';

export default function ItemDetail() {
	const { id } = useParams();
	const [item, setItem] = useState(null);

	useEffect(() => {
		axios.get(`http://localhost/api/items/${id}`).then((res) => {
			setItem(res.data);
		});
	}, [id]);

	if (!item) return <p>読み込み中...</p>;

	return (
		<div>
			<h1>{item.name}</h1>
			<p>ブランド: {item.brand ?? 'なし'}</p>
			<p>価格: ￥{item.price}</p>
			<p>説明: {item.description}</p>
		</div>
	);
}
