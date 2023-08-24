const debugFB = window.location.protocol != "https:";

var SEFB = {
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
		const button = React.createElement('div', { key : 'fb-button', className : 'fb-login-button', 'data-size' : 'large', 'data-button-type' : "login_with", 'data-layout' : 'default', 'data-auto-logout-link' : 'true', 'data-use-continue-as' : 'true' });
		clearTimeout(FBParser);
		FBParser = setTimeout(() => {
			FB.XFBML.parse();
		}, 100);
		return button;
	},
	logout			: () => {
		FB.logout((res) => {
			// console.log('Log Out', res);
			SEFB.fb_auth = {};
			SEFB.account.changeState();
		});
	},
	checkLoginState	: () => {
		if(debugFB) {
			// testing
			if(!Object.keys(SEFB.fb_auth).length) SEFB.fb_auth = {
				userID 	: 3,
				name	: 'User 3',
				db_id	: 0
			}
			console.log('Debug Log In', SEFB.fb_auth.userID);
			SEFB.login();
			return;
		}
		FB.getLoginStatus(function(response) {
			statusChangeCallback(response);
		});
	},
	login		: () => {
		if(SEFB.fb_auth.db_id) return;
		let data = {
			fb_id : SEFB.fb_auth.userID,
			name  : SEFB.fb_auth.name
		};
		SEFB.fb_auth.db_id = $.ajax({
			url 	: `http://localhost/api/users`,
			type 	: 'POST',
			data	: data,
			success : (res) => {
				res = JSON.parse(res);
				console.log('Login', res);
				return;
				if(res.data.results == undefined) {
					return;
				}
				if(res.data.results.length == 0) {
					// ToDo :: create new user
					console.log('Facebook User not found', SEFB.fb_auth.userID);
					
					FB.api(
					    `/${SEFB.fb_auth.userID}/`, (res) => {
							if(!res || res.error || !res.name) return false;
							SEFB.fb_auth.name = res.name;
							
							const data = {
								User : {
									display_name	: SEFB.fb_auth.name,
									facebook_id		: SEFB.fb_auth.userID
								}
							};
							
							$.ajax({
								url		: `${Gallery.requests.register}.json`,
								type	: 'POST',
								data	: data,
								success	: (res) => {
									const data = JSON.parse(res);
									console.log('Registered', data.data.entry_id);
									SEFB.fb_auth.db_id = data.data.entry_id;
									for(x in Gallery.comments) {
										Gallery.comments[x].login();
									}
									SEFB.account.changeState();
								}
							});
							
						}
					);
					
					
					
					SEFB.fb_auth.db_id = 0;
					return;
				}
				if(res.data.results.length != 1) {
					// unknown error
					SEFB.fb_auth.db_id = 0;
					return;
				}
				SEFB.fb_auth.db_id = res.data.results[0];
				for(x in Gallery.comments) {
					Gallery.comments[x].login();
				}
				SEFB.account.changeState();
				console.log('Logged in', SEFB.fb_auth.db_id); 
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
		return SEFB.isLoggedIn() ? React.createElement('div', { className : 'replies-button', onClick : SEFB.logout }, 'Log Out') : '';
	}
}