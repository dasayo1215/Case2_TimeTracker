import { Link } from 'react-router-dom';
import logo from '/logo.svg';

export default function GuestHeader() {
	return (
		<header className="header">
			<div className="header-wrapper">
				<h1 className="sr-only">Case2_TimeTracker</h1>
				<Link className="header-logo" to="/">
					<img className="header-logo-img" src={logo} alt="ロゴ" />
				</Link>
			</div>
		</header>
	);
}
