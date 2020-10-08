import './button.scss'

const Button = (props) => (
	<button	
		className={`pw__button ${props.type}`}>{props.children}</button>
);

Button.defaultProps = {
	type: 'primary'
};

export default Button;