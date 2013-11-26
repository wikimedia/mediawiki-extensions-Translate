After("@stash") do |scenario|
	visit(StashPage, :using_params => {:extra => "integrationtesting=deactivatestash"})
end
