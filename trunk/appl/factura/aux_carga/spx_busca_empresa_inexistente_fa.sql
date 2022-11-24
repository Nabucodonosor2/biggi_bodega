CREATE PROCEDURE [dbo].[spx_busca_empresa_inexistente_fa]
AS
BEGIN  
	declare @nro_fa		numeric
			,@count			numeric
			,@rut			numeric

	declare @TEMPO TABLE 	 
				(nro_fa numeric
				,rut numeric)

	declare c_aux_fa cursor for 
	select distinct convert(numeric, substring(RUT_CLIENTE, 1, len(RUT_CLIENTE) - 1)) rut
	from aux_factura

	open c_aux_fa 
	fetch c_aux_fa into @rut
	while @@fetch_status = 0 begin
		select @count=count(*) from empresa where rut = @rut
		if (@count=0) begin
			select top 1 @nro_fa=numero_factura from aux_factura where convert(numeric, substring(RUT_CLIENTE, 1, len(RUT_CLIENTE) - 1))=@rut
			insert into @TEMPO values (@nro_fa, @rut)
		end

		fetch c_aux_fa into @rut
	end
	close c_aux_fa
	deallocate c_aux_fa

	select * from @TEMPO
END
go