class LoginPage
	include PageObject

	include URL
	page_url URL.url('Special:UserLogin')

	text_field(:username, id: 'wpName1')
	text_field(:password, id: 'wpPassword1')
	button(:login, id: 'wpLoginAttempt')

	def login_with(username, password)
		self.username = username
		self.password = password
		login
	end
end
