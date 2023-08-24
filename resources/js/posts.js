'use string';

const e = React.createElement;

let Gallery = {
	actions 	: {
		local	: {
			api		: 'api',
			submit	: 'submit'	,
			post	: 'post',
		},
		remote	: {
			browse	: 'browse',
			image	: 'image',
			search	: 'find',
			create	: 'new'	
		}
	},
	modules		: {
		post	: 'CollectionPostItem',
		comment	: 'CollectionPostItemComment',
		user	: 'User'
	},
	user_fb_id	: 'facebook_id',
	open_post_id: 0,
	comment_id	: 253,
	comments	: {},
	posts		: {},
	page_limit	: 20,
	image_base	: 'data:image/png;base64,',
	login		: () => {
		if(SEFB.fb_auth.db_id) return;
		const key = `${Gallery.modules.user}|${Gallery.user_fb_id}`;
		let data = {};
		data[key] = SEFB.fb_auth.userID;
		SEFB.fb_auth.db_id = $.ajax({
			url 	: `${Gallery.requests.search}.json`,
			type 	: 'POST',
			data	: data,
			success : (res) => {
				res = JSON.parse(res);
				// console.log('Login', res);
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

Gallery.requests = {
	posts		: `/${Gallery.actions.local.api}/${Gallery.actions.remote.browse}/${Gallery.modules.post}`,
	image		: `/${Gallery.actions.local.api}/${Gallery.actions.remote.image}`,
	comments	: `/${Gallery.actions.local.api}/${Gallery.actions.remote.browse}/${Gallery.modules.comment}`,
	send		: `/${Gallery.actions.local.submit}/${Gallery.actions.remote.create}/${Gallery.modules.comment}`,
	search		: `/${Gallery.actions.local.api}/${Gallery.actions.remote.search}`,
	register	: `/${Gallery.actions.local.submit}/${Gallery.actions.remote.create}/${Gallery.modules.user}`
};

class Posts extends React.Component {
	constructor(props) {
		super(props);
		this.posts = {};
		this.items = {
			entries 	: [],
			names		: {},
			data		: {},
			total_count	: 0
		};
		this.state = {
			error 		: null,
			isLoaded 	: false,
			fetching	: false,
			active		: 0,
			page		: 0
		};
		Gallery.gallery = this;
	}
	
	pushHistory(post_id) {
		history.pushState({}, null, `/${Gallery.actions.local.post}/${post_id}`);
	}
	
	openPost(post_id) {
		const { data } = this.items;
		if(data[`${Gallery.modules.post}.image`] == undefined || data[`${Gallery.modules.post}.image`][post_id] == undefined) {
			if(this.hasMorePages()) this.fetchPage();
			return false;	
		}
		
		document.getElementsByTagName('body')[0].style.overflow = 'hidden';
		scrollHeader(1);
		
		if(Gallery.posts[post_id] != undefined) {
			// console.log('Open Post', this.posts[post_id]);
			Gallery.posts[post_id].open();
			this.pushHistory(post_id);
			return true;
		}
		
		const { key, desc, price, image } = this.getDetails(post_id);
		this.posts[post_id] = e(Post, { key : `post${post_id}`, post_id : post_id, image_id : key, desc : desc, price : price, image : image, container : this });
		// console.log('Add Post', this.posts);
		this.setState({
			active : post_id
		});
		this.pushHistory(post_id);
		return true;
	}
	
	getDetails(post_id) {
		const { items } = this;
		const key = items.data[`${Gallery.modules.post}.image`][post_id];
		const desc = items.data[`${Gallery.modules.post}.description`][post_id];
		const price = Maho.number(items.data[`${Gallery.modules.post}.price`][post_id], 2);
		const image = items.names[`image|${key}`].image;
		return { key : key, desc : desc, price : price, image : image };
	}
	
	closePost() {
		this.setState({
			active : 0
		});
		document.getElementsByTagName('body')[0].style.overflow = 'auto';
		scrollHeader(1);
		history.pushState({}, null, '/');
	}
	
	fetchPage(page) {
		if(page == undefined || page < 1) page = this.state.page + 1;
		if(page <= this.state.page) return false;
		this.setState({fetching : true, page : page });
		fetch(`${Gallery.requests.posts}.json?direction=DESC&sort=date_updated&limit=${Gallery.page_limit}&page=${page}`)
    	.then(res => res.json())
      	.then(
	       	(result) => {
				const { data, entries, names, total_count } = result.data;
				for(var x in entries) this.items.entries.push(entries[x]);
				for(var x in names) this.items.names[x] = names[x];
				this.items.total_count = total_count;
				for(var x in data) {
					if(this.items.data[x] == undefined) this.items.data[x] = {};
					for(var y in data[x]) this.items.data[x][y] = data[x][y];
				}
				// console.log('Posts', this.items);
          		this.setState({
            		isLoaded	: true,
					fetching	: false,
					page		: page
          		});
				if(Gallery.open_post_id && this.openPost(Gallery.open_post_id)) Gallery.open_post_id = 0;
	        },
	        // Note: it's important to handle errors here
	        // instead of a catch() block so that we don't swallow
	        // exceptions from actual bugs in components.
	        (error) => {
				console.log('Error')
        		this.setState({
	            	isLoaded: true,
					fetching	: false,
	            	error
    	      	});
	        }
      	)
		return true;
	}
	
	componentDidMount() {
    	this.fetchPage();
  	}

	hasMorePages() {
		const { page } = this.state;
		const { total_count } = this.items;
		return page * Gallery.page_limit < total_count
	}

	render() {
	    const { error, isLoaded, fetching } = this.state;
		const { entries } = this.items;

	    if (error) {
	      return e('div', {}, `Error: ${error.message}`);
	    } 

		let objs = [];

		if(Gallery.open_post_id) {
			objs.push(e('div', { key : 'blocker', className : 'blocker' }));
		}

		if (!isLoaded) {
			objs.push(SE.loading_icon);
	      return objs;
	    } 

		if(entries == undefined || !entries.length) {
			return e('div', {}, 'We do not have any posts at the moment');
		}

		for(var x in entries) {
			const item = entries[x];
			const { desc, price, image } = this.getDetails(item.Entry.id);
			const label = `${desc}\nPHP${price}`;
        	objs.push(e('img', { key : `thumb${item.Entry.id}`, src : `${Gallery.image_base}${image}`, className : 'thumb', alt : label, title : label, onClick : () => this.openPost(item.Entry.id) }));
		}

		for(var x in this.posts) {
			objs.push(this.posts[x]);
		}
		
		if(fetching) {
			objs.push(SE.loading_icon);
		} else if(this.hasMorePages()) {
			objs.push(e('div', { key : 'next', className : 'next-items', onClick : () => this.fetchPage() }, 'Show More'));
		} else {
			objs.push(e('div', { key : 'end', className : 'next-items' }, ['You are at the end of our list. ', e('a', { key : 'page-link', href : SE.page_url, title : 'Go to our page!', target : '_blank'}, 'Follow us'), ' to be notified for new items!' ]));
		}
		
		// console.log('Render Posts', this.posts, objs);
    	return e('div', {}, objs);
  	}
}

class Post extends React.Component {
	constructor(props) {
		// console.log('New Post', props.post_id);
		super(props);
		this.state = {
			post_id  	: props.post_id,
			image_id 	: props.image_id,
			desc	 	: props.desc,
			price	 	: props.price,
			container	: props.container,
			image	 	: props.image,
			open	 	: true,
			content		: [ 
				props.desc.split(/\r\n/g).map(line => e('div', { key : line }, line)),
				e('div', { key : 'price' }, `Price : PHP${props.price}`),
				e(Comments, { key : 'comments', post_id : props.post_id })
			]
		};
		Gallery.posts[props.post_id] = this;
	}
	
	open() {
		this.setState({ open : true });
	}
	
	close() {
		this.state.container.closePost();
		this.setState({ open : false });
	}
	/*
	componentDidMount() {
		const { desc, price, post_id } = this.state;
    	fetch(`${Gallery.requests.image}/${this.state.image_id}.json`)
    	.then(res => res.json())
      	.then(
	       	(result) => {
				// console.log('Image', result);
          		this.setState({
            		image 	: result.data.image,
					content	: [ 
						desc.split(/\r\n/g).map(line => e('div', { key : line }, line)),
						e('div', { key : 'price' }, `Price : PHP${price}`),
						e(Comments, { key : 'comments', post_id : post_id })
					]
          		});
	        },
	        // Note: it's important to handle errors here
	        // instead of a catch() block so that we don't swallow
	        // exceptions from actual bugs in components.
	        (error) => {
				console.log('Error')
        		this.setState({
	            	isLoaded: true,
	            	error
    	      	});
	        }
      	);
  	}
	*/
	render() {
		const { post_id, open, content, image } = this.state;
		return !open ? '' : e('div', { className : 'post', post_id : post_id }, [
			e('div', {key : 'blocker', className : 'blocker', onClick : () => this.close() }),
			image ? e('img', {key : 'image', className : 'post', src : `${Gallery.image_base}${image}`, alt : 'Item Image' }) : e('div', { key : 'placeholder' }, SE.loading_icon),
			e('div', { key : 'desc', className : 'desc' }, content ? content : SE.loading_icon),
			e('div', {key : 'closer', className : 'closer', onClick : () => this.close() }, 'x')
		]);
	}
}

class Comments extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			post_id  	: props.post_id,
			nested	 	: props.nested,
			showCount	: props.nested,
			showForm	: !props.nested,
			replies		: props.replies,
			isLoaded	: false,
			canComment	: SEFB.isRegistered(),
			items		: {}
		};
		
		Gallery.comments[props.post_id] = this;
	}
	
	hideForm() {
		if(this.state.nested) this.setState({ showForm : false });
	}
	
	login() {
		this.setState({canComment : true});
		this.update();
	}
	
	update() {
		// console.log('Update Comments');
		if(!SEFB.isRegistered()) return false;
		fetch(`${Gallery.requests.comments}/${Gallery.comment_id}/${this.state.post_id}.json?sort=date_created&direction=DESC`)
    	.then(res => res.json())
      	.then(
	       	(result) => {
				// console.log('Results', result);
				let up = {
            		isLoaded	: true,
					showCount 	: false,
            		items		: result.data
				}
				if(this.state.nested && result.data.entries != undefined && Object.keys(result.data.entries).length) {
					up.replies = Object.keys(result.data.entries).length;
				}
          		this.setState(up);
	        },
	        // Note: it's important to handle errors here
	        // instead of a catch() block so that we don't swallow
	        // exceptions from actual bugs in components.
	        (error) => {
				console.log('Error', error)
        		this.setState({
	            	isLoaded	: true,
	            	error
    	      	});
	        }
      	)
	}
	
	componentDidMount() {
		if(SEFB.isRegistered() && typeof this.state.replies == 'undefined') this.update();
		else if(!SEFB.isRegistered()) SEFB.checkLoginState();
  	}

	render() {
		const { error, isLoaded, items, post_id, nested, showCount, replies, showForm } = this.state;
		
		if(!SEFB.isLoggedIn()) {
			return e('div', {}, [ 'Log in to be able to view and post comments.', SEFB.button() ]);
		}
		if(!SEFB.isRegistered()) {
			return '';
		}
		
		let options = { className : 'comments'};
		
		if(error) {
			return e('div', options, 'An error occurred');
		}
		
		if(!isLoaded && !showCount) {
			return e('div', options, SE.loading_icon);
		}

		let objs = [];
		
		if(replies > 0 && showCount) {
			objs.push(e('div', { key : 'count', className : 'replies-button', onClick : () => {
				this.setState({showCount : false});
				this.update()
			}}, Maho.number(replies) + ' repl' + (replies == 1 ? 'y' : 'ies')));
		} else if(nested && replies > 0) {
			objs.push(e('div', { key : 'hide', className : 'replies-button', onClick : () => {
				this.setState({showCount : true });
			}}, 'Hide conversation'));
		}
		
		if(items.entries != undefined && Object.keys(items.entries).length && !showCount) {
			objs = objs.concat(items.entries.map(item => {
				const comment = items.data[`${Gallery.modules.comment}.comment`][item.Entry.id];
				const time = item.Entry.date_created;
				const user = items.data[`${Gallery.modules.comment}.posted_by`][item.Entry.id] ? items.data[`${Gallery.modules.comment}.posted_by`][item.Entry.id] : 'Unknown User';
				const replies = items.data[`${Gallery.modules.comment}.replies`][item.Entry.id].split('<br />').filter((content) => { return content.length; }).length;
				return e(Comment, { key : item.Entry.id , post_id : item.Entry.id , comment : comment, user : user, replies : replies, time : time });
			}));
		}
		
		if(!SEFB.isLoggedIn() && !nested) {
			objs = objs.concat(SEFB.button());
		} else if(SEFB.isRegistered()) {
			objs = objs.concat(showForm? e(CommentForm, { key : 'form', post_id : post_id, nested : nested, update : this }) : e('div', { key : 'reply', className : 'replies-button', onClick : () => {
				for(var x in Gallery.comments) Gallery.comments[x].hideForm();
				this.setState({showForm : true});
			}}, 'Reply'));
		}
		
		return e('div', options, objs);
	}
}

class Comment extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			post_id  : props.post_id,
			comment  : props.comment,
			user	 : props.user,
			replies  : props.replies,
			time 	 : props.time,
			form	 : false
		};
	}
	
	render() {
		const { post_id, comment, user, replies, time } = this.state;
		return e('div', { key : 'container', className : 'comment' }, [
			e('div', { key : 'user' , className : 'user' }, user),
			e('div', { key : 'time' , className : 'time' }, SE.time(Date.parse(time))),
			e('div', { key : 'comment' , className : 'content' }, comment),
			e(Comments, { key : 'replies', post_id : post_id, nested : true , replies : replies})
		]);
	}
}

class CommentForm extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			post_id  	: props.post_id,
			user_id 	: 0,
			focus  		: false,
			nested		: props.nested,
			value		: '',
			pressed		: false,
			submitted	: false,
			update		: props.update
		};
	}
	
	render() {
		const { post_id, focus, nested, submitted, value, pressed, update } = this.state;
		if(!SEFB.isLoggedIn()) {
			return nested ? '' : SEFB.button();
		}
		if(!SEFB.isRegistered()) {
			return '';
		}
		return submitted ? 'Message Submitted' : e('div', { className : 'comment-form' }, [
			e('textarea', { key : 'text', className : (focus ? 'focus' : ''), onFocus : () => {
				this.setState({ focus : true });
			}, onBlur : (evt) => {
				const obj = this;
				this.setState({ value : evt.target.value.trim() });
				setTimeout(() => {
					obj.setState({ focus : false });
				}, 500);
			}, disabled : pressed }),
			e('div', { key : 'button', disabled : pressed, className : 'form-submit', onClick : () => {
				if(!value.length) return;
				// Todo submit comment
				const obj = this;
				this.setState({ pressed : true });
				let data = {};
				data[Gallery.modules.comment] = { attach_to : post_id , comment : value , user : SEFB.fb_auth.db_id };
				$.ajax({
					url		: `${Gallery.requests.send}.json`,
					type	: 'POST',
					data	: data,
					success	: (res) => {
						// console.log('Submission', res);
						obj.setState({submitted : true});
						if(!update) return;
						update.update();
					}
				});
			}}, 'Send')
		]);
	}
}
