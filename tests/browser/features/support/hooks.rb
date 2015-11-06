After("@stash")
	visit(StashPage, :using_params => {:extra => "integrationtesting=deactivatestash"})
end
