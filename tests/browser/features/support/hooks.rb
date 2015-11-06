After("@stash") do |_scenario|
	visit(StashPage, :using_params => {:extra => "integrationtesting=deactivatestash"})
end
