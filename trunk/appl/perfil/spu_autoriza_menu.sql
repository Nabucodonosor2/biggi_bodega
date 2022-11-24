------------------spu_autoriza_menu----------------
CREATE PROCEDURE [dbo].[spu_autoriza_menu](@ve_cod_perfil		numeric
										  ,@ve_cod_item_menu	varchar(10)
										  ,@ve_lectura 			varchar(1)
										  ,@ve_escritura		varchar(1)
										  ,@ve_impresion		varchar(1)
										  ,@ve_exportar			varchar(1)
										  ,@ve_tabs				varchar(500))
AS
BEGIN

	DECLARE @vl_count numeric,
			@count_tab numeric,
			@vl_AUTORIZA_MENU varchar(1),
			@pos int,
			@cod_item_tabs varchar(100),
			@autoriza_menu varchar(100)	

	if	(@ve_escritura = 'S')
		set	@vl_AUTORIZA_MENU = 'E'
	else if	(@ve_lectura = 'S') 
		set	@vl_AUTORIZA_MENU = 'L'
	else
		set @vl_AUTORIZA_MENU = 'N'
	
	select	@vl_count =count(*) 
	from	autoriza_menu
	where	cod_perfil = @ve_cod_perfil and
			cod_item_menu = @ve_cod_item_menu


	if (@vl_count = 0) 
			insert into 
					autoriza_menu (cod_perfil, cod_item_menu, autoriza_menu, impresion, exportar)
			values		(@ve_cod_perfil, @ve_cod_item_menu, @vl_AUTORIZA_MENU, @ve_impresion, @ve_exportar)
	
	else
			update	autoriza_menu
			set		autoriza_menu = @vl_AUTORIZA_MENU
					,impresion = @ve_impresion
					,exportar = @ve_exportar
			where	cod_perfil = @ve_cod_perfil and
					cod_item_menu = @ve_cod_item_menu
	
	while (@ve_tabs<>'')
		begin
			set @pos = CHARINDEX('|', @ve_tabs) 
			set @cod_item_tabs = substring(@ve_tabs, 1, @pos - 1) 
			set @ve_tabs = substring(@ve_tabs, @pos + 1, len(@ve_tabs) - @pos)
		
			set @pos = CHARINDEX('|', @ve_tabs) 
			set @autoriza_menu = substring(@ve_tabs, 1, @pos - 1) 
			set @ve_tabs = substring(@ve_tabs, @pos + 1, len(@ve_tabs) - @pos)
		
			select	@count_tab =count(*) 
			from	autoriza_menu
			where	cod_perfil = @ve_cod_perfil and
					cod_item_menu = @cod_item_tabs

			if (@count_tab=0)
				insert into autoriza_menu (cod_perfil, cod_item_menu, autoriza_menu, impresion)
				values (@ve_cod_perfil, @cod_item_tabs, @autoriza_menu, 'N')
			else
				update	autoriza_menu
				set		autoriza_menu = @autoriza_menu
				where	cod_perfil = @ve_cod_perfil and
						cod_item_menu = @cod_item_tabs

		end


END
go