After('@stash') do
  visit(StashPage, using_params: { extra: 'integrationtesting=deactivatestash' })
end
