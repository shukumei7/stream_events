const debugFB = window.location.protocol != "https:";

var SEFB = {
	api_url			: `http://localhost/api/`,
	root			: null,
	fb_version		: 'v17.0',
    fb_id			: '339346735092648',
    fb_auth			: {},
	account			: null,
	isLoggedIn 		: () => {
		return SEFB.fb_auth.userID != undefined && !isNaN(SEFB.fb_auth.userID) ? SEFB.fb_auth.userID : false;
	},
	isRegistered 	: () => {
		return SEFB.fb_auth.db_id != undefined && !isNaN(SEFB.fb_auth.db_id) ? SEFB.fb_auth.db_id : false;
	},
	button			: () => {
		if(debugFB) return React.createElement(DebugLogin);
		const button = React.createElement('div', { key : 'fb-button', className : 'fb-login-button', 'data-size' : 'large', 'data-button-type' : "login_with", 'data-layout' : 'default', 'data-auto-logout-link' : 'true', 'data-use-continue-as' : 'true' });
		clearTimeout(FBParser);
		FBParser = setTimeout(() => {
			FB.XFBML.parse();
		}, 100);
		return button;
	},
	logout			: () => {
		if(debugFB) {
			SEFB.fb_auth.db_id = 0;
			SEFB.fb_auth = {};
			SEFB.account.changeState();
			return;
		}
		FB.logout((res) => {
			// console.log('Log Out', res);
			SEFB.fb_auth.db_id = 0;
			SEFB.fb_auth = {};
			SEFB.account.changeState();
		});
	},
	checkLoginState	: () => {
		if(debugFB) return;
		FB.getLoginStatus(function(response) {
			statusChangeCallback(response);
		});
	},
	login		: () => {
		if(SEFB.fb_auth.db_id) {
			console.log('Logged In', SEFB.fb_auth.db_id);
			return;
		}
		console.log('Logging In');
		let data = {
			fb_id : SEFB.fb_auth.userID,
			fb_name  : SEFB.fb_auth.name
		};
		$.ajax({
			url 	: SEFB.api_url + `users`,
			type 	: 'POST',
			data	: data,
			success : (res) => {
				// console.log('Login', res);
				if(!res.user_id) {
					console.log('Login Failed', res);
					return;
				}
				SEFB.fb_auth.db_id = res.user_id;
				SEFB.account.changeState();
				console.log('Login Successful', SEFB.fb_auth.db_id); 
			},
			error : () => {
				SEFB.fb_auth.db_id = 0;
			}
		});
	}
}

window.fbAsyncInit = function() {
    FB.init({
      appId      : SEFB.fb_id,
      cookie     : true,
      xfbml      : true,
      version    : SEFB.fb_version
    });
      
    FB.AppEvents.logPageView();   

	FB.Event.subscribe('auth.login', function(response) {
		statusChangeCallback(response);
	});

	SEFB.checkLoginState();
};

(function(d, s, id){
 var js, fjs = d.getElementsByTagName(s)[0];
 if (d.getElementById(id)) {return;}
 js = d.createElement(s); js.id = id;
 js.src = "https://connect.facebook.net/en_US/sdk.js";
 fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

function statusChangeCallback(response) {
	// console.log('FB Status', response);
  	if(response.status == 'connected') {
		SEFB.fb_auth = response.authResponse;
		SEFB.login();
	}
}

let FBParser = null;

class Account extends React.Component {
	constructor(props) {
		super(props);
		SEFB.account = this;
		this.state = {
			logged : false
		};
	}
	changeState() {
		this.setState({logged : SEFB.isLoggedIn()});
	}
	render() {
		return SEFB.isLoggedIn() ? React.createElement('div', { className : 'main-container' }, [
			React.createElement(Revenues),
			React.createElement(Followers),
			React.createElement(Sales),
			React.createElement(Events),
			React.createElement('div', { className : 'debug-login', onClick : SEFB.logout, 'key' : 'logout' }, 'Log Out')
		 ]) : SEFB.button();
	}
}

class Revenues extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			amount : 0
		};
	}
	update() {
		const obj = this;
		$.ajax({
			url	: SEFB.api_url + `revenues?user_id=` + SEFB.fb_auth.db_id,
			success	: (res) => {
				console.log('Revenues', res);
				obj.setState({ amount : res.revenue});
			}
		});
	}
	componentDidMount() {
		this.update();
	}
	render() {		
		return React.createElement('div', { className : 'top-info', 'key' : 'revenues'}, 'Revenue: ' + Maho.number(this.state.amount, 2));
	}
}

class Followers extends React.Component {
	render() {
		$.ajax({
			url	: SEFB.api_url + `followers?user_id=` + SEFB.fb_auth.db_id,
			success	: (res) => {
				console.log('Followers', res);
			}
		});
		return React.createElement('div', { className : 'top-info'}, 'Followers: 0');
	}
}

class Sales extends React.Component {
	render() {
		$.ajax({
			url	: SEFB.api_url + `sales?user_id=` + SEFB.fb_auth.db_id,
			success	: (res) => {
				console.log('Sales', res);
			}
		});
		return React.createElement('div', { className : 'top-info'}, 'Sales: 0.0');
	}
}

class Events extends React.Component {
	render() {
		$.ajax({
			url	: SEFB.api_url + `events?user_id=` + SEFB.fb_auth.db_id,
			success	: (res) => {
				console.log('Events', res);
			}
		});
		return React.createElement('div', { className : 'top-info'}, 'Nothing');
	}
}

class DebugLogin extends React.Component {
	constructor(props) {
		super(props);
		this.handleClick = this.handleClick.bind(this);
	}
	handleClick() {
		if(!Object.keys(SEFB.fb_auth).length) SEFB.fb_auth = {
			userID 	: 3,
			name	: 'User 3',
			db_id	: 0
		}
		console.log('Debug Log In', SEFB.fb_auth.userID);
		SEFB.login();
	}
	render() {
		return React.createElement('div', { 'className' : 'debug-login', 'onClick' : this.handleClick }, 'Log In');
	}
}