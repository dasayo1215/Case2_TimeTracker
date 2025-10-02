import { BrowserRouter, Routes, Route } from 'react-router-dom';
import ItemList from './pages/ItemList';
import ItemDetail from './pages/ItemDetail';

function App() {
	return (
		<BrowserRouter>
			<Routes>
				<Route path="/" element={<ItemList />} />
				<Route path="/items/:id" element={<ItemDetail />} />
			</Routes>
		</BrowserRouter>
	);
}

export default App;
