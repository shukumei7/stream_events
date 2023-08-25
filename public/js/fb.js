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
		SEFB.fb_auth.db_id = 0;
		SEFB.fb_auth = {};
		SEFB.account.changeState();
		FB.logout((res) => {
			// console.log('Log Out', res);
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
		if(typeof SEFB.fb_auth.name == 'undefined') {
			FB.api('/me',
					'GET',
					{"fields":"name"},
					  function(response) {
						  // Insert your code here
							console.log('Get Name', response);
							SEFB.fb_auth.name = response.name;
							SEFB.login();
					  }
			);
			return;
		}
		let data = {
			fb_id 	: SEFB.fb_auth.userID,
			fb_name  : SEFB.fb_auth.name
		};
		console.log('Logging In', data);
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
		return;
	}
	if(SEFB.fb_auth) SEFB.logout();
}

let FBParser = null;

const refresh_time = 5000;

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
		console.log('User Status', SEFB.isLoggedIn());
		return SEFB.isLoggedIn() ? React.createElement('div', { className : 'main-container', key : 'container' }, [
			React.createElement(Revenues, { key : 'revenues' }),
			React.createElement(Followers, { key : 'followers' }),
			React.createElement(Sales, { key : 'sales' }),
			React.createElement(Events, { key : 'events' }),
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
		if(!SEFB.fb_auth.db_id) return;
		const obj = this;
		$.ajax({
			url	: SEFB.api_url + `revenues?user_id=` + SEFB.fb_auth.db_id,
			success	: (res) => {
				// console.log('Revenues', res);
				obj.setState({ amount : res.revenue});
				setTimeout(() => {
					// console.log('Update');
					obj.update();
				}, refresh_time);
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
	constructor(props) {
		super(props);
		this.state = {
			followers : 0
		};
	}
	update() {
		if(!SEFB.fb_auth.db_id) return;
		const obj = this;
		$.ajax({
			url	: SEFB.api_url + `followers?user_id=` + SEFB.fb_auth.db_id,
			success	: (res) => {
				// console.log('Followers', res);
				obj.setState({ followers : res.followers});
				setTimeout(() => {
					// console.log('Update');
					obj.update();
				}, refresh_time);
			}
		});
	}
	componentDidMount() {
		this.update();
	}
	render() {
		return React.createElement('div', { className : 'top-info'}, 'Followers: ' + Maho.number(this.state.followers));
	}
}

class Sales extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			items : {}
		};
	}
	update() {
		if(!SEFB.fb_auth.db_id) return;
		const obj = this;
		$.ajax({
			url	: SEFB.api_url + `sales?user_id=` + SEFB.fb_auth.db_id,
			success	: (res) => {
				// console.log('Sales', res);
				obj.setState({ items : res.items});
				setTimeout(() => {
					// console.log('Update');
					obj.update();
				}, refresh_time);
			}
		});
	}
	componentDidMount() {
		this.update();
	}
	render() {
		let text = 'Nothing sold';
		const items = this.state.items;
		if(Object.keys(items).length) {
			text = [];
			for(let n in items) {
				text.push(React.createElement('div', { key : n }, n + ' : $' + Maho.number(items[n], 2)));
			}
			// console.log('Items', text);
		}
		return React.createElement('div', { className : 'top-info'}, text);
	}
}

class Events extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			events : {},
			latest : 0,
			oldest : 0,
			bottom : true,
			loading_older : false
		};
	}
	update() {
		if(!SEFB.fb_auth.db_id) return;
		const obj = this;
		$.ajax({
			url	: SEFB.api_url + `events?user_id=` + SEFB.fb_auth.db_id + `&after=` + obj.state.latest,
			success	: (res) => {
				// console.log('Newer Updates', obj.state.latest, res.updates);
				// obj.setState({ events : res.updates});				
				if(res.updates) {
					let current = obj.state.events;
					// console.log('Merge', current[0].time, res.updates[0].time);
					current = res.updates.concat(current);
					let d = null;
					obj.setState(d = {
						events : current,
						latest : current[0].time
					});
				}
				setTimeout(() => {
					// console.log('Update');
					obj.update();
				}, refresh_time);
			}
		});
	}
	getOlder() {
		if(this.state.loading_older) return;
		const obj = this;
		this.setState({ loading_older : true });
		// console.log('Getting Older than', this.state.oldest);
		$.ajax({
			url	: SEFB.api_url + `events?user_id=` + SEFB.fb_auth.db_id + `&before=` + this.state.oldest,
			success	: (res) => {
				if(res.updates) {
					let current = obj.state.events;
					// console.log('Merge', current[0].time, res.updates[0].time);
					current = current.concat(res.updates);
					let d = null;
					obj.setState(d = {
						events : current,
						loading_older : false,
						oldest : current[current.length- 1].time
					});
					// console.log('Show More Events', d);
				}
			},
			error : () => {
				obj.setState({ loading_older : false });
			}
		});
	}
	componentDidMount() {
		const obj = this;
		$.ajax({
			url	: SEFB.api_url + `events?user_id=` + SEFB.fb_auth.db_id,
			success	: (res) => {
				// console.log('Events', res);
				if(!res.updates.length) {
					setTimeout(() => {
						// console.log('Update');
						obj.update();
					}, refresh_time);
					obj.setState({
						latest : Date.now(),
						oldest : Date.now()
					});
				}
				let d = null;
				obj.setState(d = { 
					events : res.updates,
					latest : res.updates[0].time,
					oldest : res.updates[res.updates.length - 1].time
				});
				// console.log('Loaded Events', d);
				setTimeout(() => {
					// console.log('Update');
					obj.update();
				}, refresh_time);
				setTimeout(() => {
					const box = $('.event-info');
					box.on('scroll', null, (ev) => {
						const pos = box.scrollTop();
						const height = box.prop('scrollHeight') - box.height();
						// console.log('Scrolled', pos, height);
						if(pos >= height * 0.95) {
							// console.log('Scrolled to bottom');
							obj.getOlder();
						}
					});
				}, 100);
			}
		});
	}
	render() {
		let text = 'Nothing';
		const events = this.state.events;
		if(Object.keys(events).length) {
			text = [];
			for(let n in events) {
				events[n].key = events[n].table + ':' + events[n].table_id;
				// console.log('Add Event', events[n]);
				text.push(React.createElement(EventLine, events[n]));
			}
		}
		return React.createElement('div', { className : 'event-info'}, text);
	}
}

class EventLine extends React.Component {
	constructor(props) {
		super(props);
		// console.log('Event Line', props);
		this.state = {
			table_id : props.table_id,
			time	 : props.time,
			table	 : props.table,
			message	 : props.message,
			read	 : props.read
		};
	}
	click(obj) {
		if(!SEFB.fb_auth.db_id) return;
		const data = {
			table_id : obj.state.table_id,
			table	 : obj.state.table,
			user_id	 : SEFB.fb_auth.db_id
		};
		$.ajax({
			url		: SEFB.api_url + `flags`,
			type	: 'post',
			data 	: data,
			success	: (res) => {
				obj.setState({ read : res.flag});
			}
		});
	}
	render() {
		let className = '';
		const obj = this;
		if(this.state.read) className = 'event-read';
		return React.createElement('div', { className : className, onClick : () => { obj.click(obj) }, key : this.state.table + ':' + this.state.table_id }, [
			this.state.message,
			React.createElement('span', { key : 'time' }, this.state.time)
		]);
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